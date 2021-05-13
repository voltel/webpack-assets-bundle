<?php


namespace Tests\Service;


use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Voltel\WebpackAssetsBundle\Service\EntrypointAssetsRegistryService;

class EntrypointAssetsRegistryServiceTest extends KernelTestCase
{
    /** @var EntrypointAssetsRegistryService|null  */
    private $assetsRegistry;


    protected function setUp(): void
    {
        // We basically want to initialize our bundle into an application (from HttpKernel component),
        // and check that the container has that service.
        $kernel = self::bootKernel();

        $this->assetsRegistry = $kernel->getContainer()->get('test.voltel_webpack_assets.entrypoint_registry');
    }


    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->assetsRegistry = null;
    }


    /**
     * This test will assert that "getCssAssetsRelativeUrlsForEntries"
     * will return an expected number of URLs for CSS files
     * for the one entrypoint.
     *
     * @test
     * @dataProvider provideEntryPointsWithCss
     */
    public function testGetCssAssetsRelativeUrlsForEntries(
        string $c_entrypoint_name,
        int $n_expected_urls,
        bool $l_expect_exception = false
    )
    {
        if ($l_expect_exception) {
            $this->expectException(\RuntimeException::class);
        }//endif

        $a_urls = $this->assetsRegistry->getCssAssetsRelativeUrlsForEntries($c_entrypoint_name);

        $this->assertCount($n_expected_urls, $a_urls);
    }


    /**
     * This test will assert that "getJSAssetsRelativeUrlsForEntries"
     * will return an expected number of URLs for JavaScript files
     * for the one entrypoint.
     *
     * @test
     * @dataProvider provideEntryPointsWithJS
     */
    public function testGetJSAssetsRelativeUrlsForEntries(
        string $c_entrypoint_name,
        int $n_expected_urls,
        bool $l_expect_exception = false
    )
    {
        if ($l_expect_exception) {
            $this->expectException(\RuntimeException::class);
        }//endif

        $a_urls = $this->assetsRegistry->getJsAssetsRelativeUrlsForEntries($c_entrypoint_name);

        $this->assertCount($n_expected_urls, $a_urls);
    }


    /**
     * This test will assert that "GetCssAssetFilesContentForEntry"
     * will return a CSS string with expected substrings
     * representing separate CSS files for the provided one entrypoint.
     *
     * @test
     * @dataProvider provideEntryPointsForCssContent
     */
    public function testGetCssAssetFilesContentForEntry(
        string $c_entrypoint_name,
        array $a_css_regexp = []
    )
    {
        $c_css = $this->assetsRegistry->getCssAssetsFileContentForEntry($c_entrypoint_name);

        if (0 === count($a_css_regexp)) {
            $this->assertEmpty($c_css);
        } else {
            $this->assertNotEmpty($c_css);

            foreach ($a_css_regexp as $c_this_regexp) {
                $this->assertRegExp('/' . $c_this_regexp . '/i', $c_css);
            }
        }//endif
    }


    /**
     * This test will assert that different invocations of "GetCssAssetFilesContentForEntry" for several entrypoints
     * (i.e. entrypoints provided as an array, or a combination of string arguments)
     * return CSS string w/o an error (content of the string is not analysed).
     *
     * @test
     * @dataProvider provideArgumentsWithEntrypoints
     */
    public function testArgumentsForGetCssAssetFilesContentForEntry()
    {
        $a_arguments = func_get_args();

        // The method must accept at least one argument, may send an empty string or an empty array
        $c_css = $this->assetsRegistry->getCssAssetsFileContentForEntry(...$a_arguments);

        if (empty($a_arguments[0])) {
            $this->assertEmpty($c_css);

        } else {
            $this->assertNotEmpty($c_css);
        }
    }


    /**
     * This test will assert that matching invocations of "GetCssAssetFilesContentForEntry" for several entrypoints
     * (i.e. entrypoints provided as an array, or a combination of string arguments)
     * return the same CSS string or not.
     *
     * @test
     * @dataProvider provideTwoSetsOfArguments
     */
    public function testResultsOfGetCssAssetFilesContentForEntry(array $a_set_1, array $a_set_2, bool $l_expect_same_result)
    {
        $c_css_1 = $this->assetsRegistry->getCssAssetsFileContentForEntry(...$a_set_1);
        $c_css_2 = $this->assetsRegistry->getCssAssetsFileContentForEntry(...$a_set_2);

        if ($l_expect_same_result) {
            $this->assertSame($c_css_1, $c_css_2);
        } else {
            $this->assertNotSame($c_css_1, $c_css_2);
        }//endif
    }


    public function provideEntryPointsWithCss()
    {
        yield 'OK. Entrypoint with 2 CSS files' => ['entry_one', 2, false];
        yield 'OK. Entrypoint with 3 CSS files' => ['entry_two', 3, false];
        yield 'OK. Entrypoint with 2 CSS files only' => ['entry_css_only', 2, false];
        yield 'OK. Entrypoint with zero CSS files' => ['entry_js_only', 0, false];
        yield 'OK. Entrypoint with no files' => ['entry_no_assets', 0, false];
        yield 'Expect error: entrypoint does not exist' => ['entry_unknown', 0, true];
    }


    public function provideEntryPointsWithJs()
    {
        yield 'OK. Entrypoint with 2 JS files' => ['entry_one', 2, false];
        yield 'OK. Entrypoint with 3 JS files' => ['entry_two', 3, false];
        yield 'OK. Entrypoint with 2 JS files only' => ['entry_js_only', 2, false];
        yield 'OK. Entrypoint with zero JS files' => ['entry_css_only', 0, false];
        yield 'OK. Entrypoint with no files' => ['entry_no_assets', 0, false];
        yield 'Expect error: entrypoint does not exist' => ['entry_unknown', 0, true];
    }


    public function provideEntryPointsForCssContent()
    {
        $c_regexp_1 = 'body\s*?{\s*?background-color:';
        $c_regexp_2 = 'main\s*?{\s*?background-color:';
        $c_regexp_3 = 'section\s*?{\s*?margin-bottom:';

        yield 'OK. Entrypoint one with css' => ['entry_one', [$c_regexp_1, $c_regexp_2]];
        yield 'OK. Entrypoint two with css' => ['entry_two', [$c_regexp_1, $c_regexp_3]];
        yield 'OK. Entrypoint with only css' => ['entry_css_only', [$c_regexp_2, $c_regexp_3]];
        yield 'OK. Entrypoint with only js' => ['entry_js_only'];
        yield 'OK. Entrypoint with no files' => ['entry_no_assets'];
    }


    public function provideArgumentsWithEntrypoints()
    {
        yield 'OK. One entrypoint as a string argument' => ['entry_one'];
        yield 'OK. Two entrypoints as string arguments' => ['entry_one', 'entry_two'];
        yield 'OK. Two entrypoints as an array argument' => [['entry_one', 'entry_two']];
        yield 'OK. Three entrypoints as an array' => [['entry_one', 'entry_js_only', 'entry_two']];
        yield 'OK. Three entrypoints as string arguments' => ['entry_one', 'entry_js_only', 'entry_two'];
        yield 'OK. No entrypoints provided: empty array' => [[]];
        yield 'OK. No entrypoints provided: empty string' => [''];
        yield 'OK. No entrypoints provided: null' => [null];
    }


    public function provideTwoSetsOfArguments()
    {
        // Combination of string arguments vs. a array with the same order of arguments
        yield 'string arguments vs. array' => [
            ['entry_one', 'entry_js_only', 'entry_css_only', 'entry_no_assets', 'entry_two'],
            [['entry_one', 'entry_js_only', 'entry_css_only', 'entry_no_assets', 'entry_two']],
            true
        ];

        // Combination of string arguments vs. a combination of string arguments with changed order
        yield 'string arguments vs. string arguments with changed order' => [
            ['entry_two', 'entry_css_only'],
            ['entry_css_only', 'entry_two'],
            false
        ];

        // Combination of string arguments vs. a combination of string arguments with changed order,
        // but entrypoints w/o CSS do not affect the result
        yield 'string arguments with entrypoints w/o css vs. changed order' => [
            ['entry_two', 'entry_css_only', 'entry_js_only', 'entry_no_assets'],
            ['entry_two', 'entry_js_only', 'entry_no_assets', 'entry_css_only'],
            true
        ];
    }

}
