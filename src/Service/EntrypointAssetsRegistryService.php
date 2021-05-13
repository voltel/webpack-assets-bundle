<?php

namespace Voltel\WebpackAssetsBundle\Service;

/**
 * This service depends on the output of "webpack --json > stats.json" command
 * or similar (e.g. with "webpack-stats-plugin"),
 * that should produce a json file with a key "entrypoints".
 *
 * NB! This service requires configuration in services.yaml for its arguments.
 * Example configuration:
 *     App\Service\Webpack\AssetsChunkRegistryService:
 *          arguments: ['%kernel.project_dir%', 'stats.json', 'public/dist' ]
 */
class EntrypointAssetsRegistryService
{
    private static $entryPointRegistry;

    /**
     * Note: full filesystem path of the project root directory
     * @var string
     */
    private $projectDir;

    /**
     * Note: Relative to project root dir, e.g. "stats.json"
     * @var string
     */
    private $webpackStatsFilepath;

    /**
     * Note: Relative to project root dir, e.g. "public/dist"
     * @var string
     */
    private $webpackOutputDir;

    /**
     * Note: document root directory for public web access, usually "public"
     * @var string
     */
    private $publicDir;

    /*
     * See comments at class definition for arguments
     */
    public function __construct(
        string $c_project_root_dir, // in local file system
        string $c_webpack_stats_relative_filepath, // relative path starting from the project root "stats.json"
        string $c_app_public_dir = 'public',
        string $c_webpack_output_dir = 'dist' // "dist" or "build" in the "public" directory
    )
    {
        $this->projectDir = $c_project_root_dir;
        $this->webpackStatsFilepath = $c_webpack_stats_relative_filepath;
        $this->publicDir = $c_app_public_dir;
        $this->webpackOutputDir = $c_webpack_output_dir;
    }


    /**
     * Returns content of resulting asset CSS files for an entry point(s) from the argument.
     * Accepts an array of entrypoint names, or one or more entrypoint names as several string arguments.
     * It gets information from statistics output file of webpack (in .json format)
     * reads content of the files and concatenates content of all css files for every entry point
     * in one string to return.
     * This string can be consumed by "inline_css" Twig filter
     * to inject css styling rules in tags in an email (as expected by Gmail).
     *
     * @param string|array<string> $mix_entry_names
     * @return string
     * @throws \JsonException
     */
    public function getCssAssetsFileContentForEntry($mix_entry_names /* or entrypoint names as arguments */) : string
    {
        $c_content = '';

        $a_entry_names = is_array($mix_entry_names) ? $mix_entry_names : func_get_args();

        if (0 === count($a_entry_names)) {
            return '';
        }//endif

        $a_relative_urls = $this->getAssetsOfTypeRelativeUrlsForEntries('css', $a_entry_names);

        foreach ($a_relative_urls as $c_this_relative_url) {
            $c_this_filepath = join('/', [$this->projectDir, $this->publicDir, $c_this_relative_url]);
            //
            if (!file_exists($c_this_filepath)) {
                $c_message = sprintf('Did you forget to re-create a stats.json file during the last webpack build? Use "webpack --quiet --json > stats.json" to compile a file ready for parsing. ');
                throw new \RuntimeException(sprintf('Failed to locate webpack output file "%s" in the local filesystem for entry points "%s". ' . $c_message,
                    $c_this_filepath, implode('", "', $a_entry_names)));
            }//endif

            $c_content .= file_get_contents($c_this_filepath);
        }//endforeach

        return $c_content;
    }//end of function


    /**
     * @param string|array<string> $mix_entry_names
     * @return array
     * @throws \JsonException
     */
    public function getCssAssetsRelativeUrlsForEntries($mix_entry_names) : array
    {
        return $this->getAssetsOfTypeRelativeUrlsForEntries('css', (array) $mix_entry_names);
    }//end of function


    /**
     * @param string|array<string> $mix_entry_names
     * @return array
     * @throws \JsonException
     */
    public function getJsAssetsRelativeUrlsForEntries($mix_entry_names) : array
    {
        return $this->getAssetsOfTypeRelativeUrlsForEntries('js', (array) $mix_entry_names);
    }//end of function


    /**
     * @param string $c_assets_type either "css" or "js" string
     * @param array $a_entry_names
     * @return array
     * @throws \JsonException
     */
    private function getAssetsOfTypeRelativeUrlsForEntries(string $c_assets_type, array $a_entry_names) : array
    {
        $a_file_urls = [];

        $a_registry = $this->getRegistry();

        foreach ($a_entry_names as $c_this_entry_name) {
            // Skip empty strings
            if (empty($c_this_entry_name)) {
                continue;
            }

            if (!array_key_exists($c_this_entry_name, $a_registry)) {
                throw new \RuntimeException(sprintf('Entry "%s" is not found in the webpack output statistics file "%s"', $c_this_entry_name, $this->webpackStatsFilepath));
            }//endif

            foreach ($a_registry[$c_this_entry_name][$c_assets_type] as $c_this_filename) {
                $c_this_filepath = $this->projectDir . DIRECTORY_SEPARATOR . $this->publicDir . DIRECTORY_SEPARATOR . $this->webpackOutputDir . DIRECTORY_SEPARATOR . $c_this_filename;
                if (!file_exists($c_this_filepath)) {
                    $c_message = sprintf('Did you forget to re-create a stats.json file during the last webpack build? Use "webpack --quiet --json > stats.json" to compile a file ready for parsing. ');
                    throw new \RuntimeException(sprintf('Failed to locate webpack output file "%s" in the local filesystem for entry point "%s". ' . $c_message, $c_this_filepath, $c_this_entry_name));
                }//endif

                $a_file_urls[] = '/' . $this->webpackOutputDir . '/' . $c_this_filename;
            }//endforeach
        }//endforeach

        return array_unique($a_file_urls);
    }//end of function



    /**
     * Reads webpack statistics json file output to get information about the entries and resulting assets
     *
     * @return array
     * @throws \JsonException
     */
    private function getRegistry(): array
    {
        if (!empty(self::$entryPointRegistry)) return self::$entryPointRegistry;

        $c_full_filepath = $this->projectDir . DIRECTORY_SEPARATOR . $this->webpackStatsFilepath;
        //
        if (!file_exists($c_full_filepath)) {
            throw new \RuntimeException(sprintf('Failure to locate file with webpack statistics "%s" in the local file system', $c_full_filepath));
        }//endif


        $a_data = json_decode(file_get_contents($c_full_filepath), true);
        //
        if (json_last_error()) {
            throw new \JsonException(json_last_error_msg());
        }//endif

        $a_entry_points = $a_data['entrypoints'] ?? null;

        if (empty($a_entry_points)) {
            throw new \RuntimeException(sprintf('Absent or empty webpack statistics section "entrypoints" in file "%s"', $c_full_filepath));
        }//endif


        $a_registry = [];

        foreach ($a_entry_points as $c_entry_name => $a_entry_point_info) {
            $a_registry[$c_entry_name] = ['css' => array(), 'js' => array()];

            $a_assets_info = (array) $a_entry_point_info['assets'];

            foreach ($a_assets_info as $c_this_asset_file_name) {
                $c_ext = pathinfo($c_this_asset_file_name, PATHINFO_EXTENSION);
                $a_registry[$c_entry_name][$c_ext][] = $c_this_asset_file_name;
            }//endforeach
        }//endforeach

        self::$entryPointRegistry = $a_registry;

        return $a_registry;
    }//end of function


}//end of class
