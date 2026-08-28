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
 * The executable form of the package README's rule 1: zero `use Storm\<module>` import in
 * Contracts, even for a `@see`. A docblock-only import is invisible to deptrac, which does not
 * count use-only references, so the rule needs its own tripwire and this test is it.
 *
 * Pint's `fully_qualified_strict_types` fixer would otherwise re-import such symbols straight
 * from fully-qualified `@see` tags, reproducing the very violation the rule forbids; it is
 * neutralized in `pint.json` with `phpdoc_tags: []`, which keeps the fixer's code-level value
 * while removing its docblock reach. This test fails if that config ever regresses.
 *
 * A cross-reference to a concrete belongs in the docblock as a fully-qualified `@see \Storm\...`,
 * never as an import: at subtree-split time `chronhub/storm-contracts` must not reference classes
 * outside its own dependency set.
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
        // the flip side of the purity rule: the prescribed form, a fully-qualified `\Storm\...` in
        // the docblock, is deptrac-invisible AND build-invisible, so a rename rots it silently;
        // this walk makes each such reference cost what an import would have cost, a red build
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
        // the second half of the README's rule 3: a port's @throws names the CONTRACTED type; a
        // concrete module class slipping in would hand callers a catch target the split cannot see
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
