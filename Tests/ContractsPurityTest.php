<?php

declare(strict_types=1);

namespace Storm\Contracts\Tests;

use FilesystemIterator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

use function preg_match;
use function sprintf;
use function str_contains;

/**
 * Checks the package README's rule 1, no `use Storm\<module>` import in Contracts, and rule 3 on the
 * throws clauses of its ports.
 *
 * Rule 1 holds even for an `@see`. Deptrac does not count use-only references, so a
 * docblock-only import escapes it and this test catches it instead. Pint's
 * `fully_qualified_strict_types` fixer would re-import such symbols from fully-qualified `@see`
 * tags; `pint.json` neutralizes it with `phpdoc_tags: []`, which keeps its code-level fixes, and
 * this test fails if that setting regresses.
 *
 * A cross-reference to a concrete class goes in the docblock as a fully-qualified tag, such as
 * `@see \Storm\...`, never as an import: after the subtree split, `chronhub/storm-contracts` must not
 * reference classes outside its own dependencies.
 */
final class ContractsPurityTest extends TestCase
{
    #[Test]
    public function contracts_never_import_a_concrete_storm_type(): void
    {
        $offenders = [];

        /** @var SplFileInfo $file */
        foreach (new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(dirname(__DIR__), FilesystemIterator::SKIP_DOTS),
        ) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php' || str_contains($file->getPathname(), '/Tests/')) {
                continue;
            }

            foreach (file($file->getPathname()) ?: [] as $line => $content) {
                if (preg_match('#^use Storm\\\\(?!Contracts\\\\)#', $content) === 1) {
                    $offenders[] = sprintf('%s:%d %s', $file->getPathname(), $line + 1, trim($content));
                }
            }
        }

        $this->assertSame([], $offenders, "Contracts must stay pure — no `use Storm\\<module>` import, even for a @see (write it fully-qualified in the docblock instead):\n".implode("\n", $offenders));
    }

    #[Test]
    public function every_fully_qualified_doc_reference_resolves(): void
    {
        // a fully-qualified `\Storm\...` doc reference is invisible to deptrac and to the build, so a
        // rename breaks it silently; this walk turns each one into a red build, as an import would
        $offenders = [];

        foreach (self::sourceFiles() as $file) {
            $content = file_get_contents($file->getPathname());
            if ($content === false) {
                continue;
            }

            if (preg_match_all('#\\\\Storm\\\\[A-Za-z0-9_\\\\]+#', $content, $matches) > 0) {
                foreach (array_unique($matches[0]) as $reference) {
                    $symbol = ltrim((string) preg_replace('#::.*$#', '', $reference), '\\');

                    if (! class_exists($symbol) && ! interface_exists($symbol) && ! trait_exists($symbol) && ! enum_exists($symbol)) {
                        $offenders[] = sprintf('%s: %s', $file->getFilename(), $reference);
                    }
                }
            }
        }

        $this->assertSame([], $offenders, "Every fully-qualified `\\Storm\\...` doc reference must resolve — a renamed or moved target breaks no build, so this test is the tripwire:\n".implode("\n", $offenders));
    }

    #[Test]
    public function contracted_throws_never_name_a_concrete_module_type(): void
    {
        // the README's rule 3, second half: a port's @throws names the contracted type, since a
        // concrete module class would give callers a catch target the split cannot see
        $offenders = [];

        foreach (self::sourceFiles() as $file) {
            foreach (file($file->getPathname()) ?: [] as $line => $content) {
                if (preg_match('#@throws\s+\\\\?Storm\\\\(?!Contracts\\\\)#', $content) === 1) {
                    $offenders[] = sprintf('%s:%d %s', $file->getFilename(), $line + 1, trim($content));
                }
            }
        }

        $this->assertSame([], $offenders, "A Contracts port's @throws must name a Contracts type, never a concrete module class:\n".implode("\n", $offenders));
    }

    #[Test]
    public function contracted_throws_never_declare_a_throwable_tail(): void
    {
        // the README's rule 3, applied to driver failures: a tail grants the caller no exhaustiveness,
        // so the implementation translates its driver failure into a contracted type before it crosses
        $offenders = [];

        foreach (self::sourceFiles() as $file) {
            foreach (file($file->getPathname()) ?: [] as $line => $content) {
                if (preg_match('#@throws\s+(?:[\w\\\\]+\|)*\\\\?Throwable(?![\w\\\\])#', $content) === 1) {
                    $offenders[] = sprintf('%s:%d %s', $file->getFilename(), $line + 1, trim($content));
                }
            }
        }

        $this->assertSame([], $offenders, "A Contracts port's @throws must name a contracted type, never a Throwable tail:\n".implode("\n", $offenders));
    }

    /**
     * @return list<SplFileInfo>
     */
    private static function sourceFiles(): array
    {
        $files = [];

        /** @var SplFileInfo $file */
        foreach (new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(dirname(__DIR__), FilesystemIterator::SKIP_DOTS),
        ) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php' && ! str_contains($file->getPathname(), '/Tests/')) {
                $files[] = $file;
            }
        }

        return $files;
    }
}
