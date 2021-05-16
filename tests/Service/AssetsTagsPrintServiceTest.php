<?php


namespace Tests\Service;


use SebastianBergmann\CodeCoverage\Report\PHP;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Setup\Kernel\VoltelWebpackAssetsTestingKernel;
use Voltel\WebpackAssetsBundle\Service\AssetsTagsPrintService;
use Voltel\WebpackAssetsBundle\Service\EntrypointAssetsRegistryService;

class AssetsTagsPrintServiceTest extends KernelTestCase
{

    /** @var AssetsTagsPrintService */
    private $printTagsService;

    protected function setUp(): void
    {
        // We basically want to initialize our bundle into an application (from HttpKernel component),
        // and check that the container has that service.
        $kernel = self::bootKernel();

        $this->printTagsService = $kernel->getContainer()->get('test.voltel_webpack_assets.print_tags');
    }


    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->printTagsService = null;
    }


    /**
     * This test will assert that "printCssLinkTagsForEntries"
     * prints <link href=""> tags for entrypoint(s)
     *
     * @test
     * @dataProvider provideEntryPointsWithCssForTagPrint
     */
    public function testPrintCssLinkTagsForEntries(
        $mix_entrypoint_names,
        int $n_expected_urls,
        bool $l_expect_exception = false
    )
    {
        if ($l_expect_exception) {
            $this->expectException(\Exception::class);
        }//endif

        $c_html_markup_relative = $this->printTagsService->printCssLinkTagsForEntries((array) $mix_entrypoint_names);
        $c_html_markup_absolute = $this->printTagsService->printCssLinkTagsForEntries((array) $mix_entrypoint_names, true);

        if ($n_expected_urls === 0) {
            $this->assertEmpty($c_html_markup_relative);

        } else {
            $this->assertStringContainsString('<link rel="stylesheet" ', $c_html_markup_relative);
            $this->assertNotSame($c_html_markup_relative, $c_html_markup_absolute);
            $this->assertStringContainsString(VoltelWebpackAssetsTestingKernel::FRAMEWORK_ROUTER_DEFAULT_URI, $c_html_markup_absolute);
            $this->assertSame($c_html_markup_relative, str_replace(VoltelWebpackAssetsTestingKernel::FRAMEWORK_ROUTER_DEFAULT_URI, '', $c_html_markup_absolute));
        }//endif
    }


    /**
     * This test will assert that "printJsScriptTagsForEntries"
     * prints <script src=""> tags for entrypoint(s)
     *
     * @test
     * @dataProvider provideEntryPointsWithJsForTagPrint
     */
    public function testPrintJsScriptTagsForEntries(
        $mix_entrypoint_names,
        int $n_expected_urls,
        bool $l_expect_exception = false
    )
    {
        if ($l_expect_exception) {
            $this->expectException(\Exception::class);
        }//endif

        $c_html_markup_relative = $this->printTagsService->printJsScriptTagsForEntries((array) $mix_entrypoint_names);
        $c_html_markup_absolute = $this->printTagsService->printJsScriptTagsForEntries((array) $mix_entrypoint_names, true);

        if ($n_expected_urls === 0) {
            $this->assertEmpty($c_html_markup_relative);

        } else {
            $this->assertStringContainsString('<script src="', $c_html_markup_relative);
            $this->assertNotSame($c_html_markup_relative, $c_html_markup_absolute);
            $this->assertStringContainsString(VoltelWebpackAssetsTestingKernel::FRAMEWORK_ROUTER_DEFAULT_URI, $c_html_markup_absolute);
            $this->assertSame($c_html_markup_relative, str_replace(VoltelWebpackAssetsTestingKernel::FRAMEWORK_ROUTER_DEFAULT_URI, '', $c_html_markup_absolute));
        }//endif
    }



    public function provideEntryPointsWithCssForTagPrint()
    {
        yield 'OK. Entrypoint with 2 CSS files' => ['entry_one', 2, false];
        yield 'OK. Entrypoint with 3 CSS files' => ['entry_two', 3, false];
        yield 'OK. Array of one entrypoint ' => [['entry_one'], 2, false];
        yield 'OK. Array of two entrypoints ' => [['entry_one', 'entry_two'], 5, false];
        yield 'OK. Entrypoint with 2 CSS files only' => ['entry_css_only', 2, false];
        yield 'OK. Entrypoint with zero CSS files' => ['entry_js_only', 0, false];
        yield 'OK. Entrypoint with no files' => ['entry_no_assets', 0, false];
        yield 'Expect error: entrypoint does not exist' => ['entry_unknown', 0, true];
    }


    public function provideEntryPointsWithJsForTagPrint()
    {
        yield 'OK. Entrypoint with 2 JS files' => ['entry_one', 2, false];
        yield 'OK. Entrypoint with 3 JS files' => ['entry_two', 3, false];
        yield 'OK. Array of one entrypoint ' => [['entry_one'], 2, false];
        yield 'OK. Array of two entrypoints ' => [['entry_one', 'entry_two'], 5, false];
        yield 'OK. Entrypoint with 2 JS files only' => ['entry_js_only', 2, false];
        yield 'OK. Entrypoint with zero JS files' => ['entry_css_only', 0, false];
        yield 'OK. Entrypoint with no files' => ['entry_no_assets', 0, false];
        yield 'Expect error: entrypoint does not exist' => ['entry_unknown', 0, true];
    }

}
