<?php

namespace App\Traits;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;

trait View
{

    use Request, Model;

    /**
     * @param string $view relative path to twig template
     * @param array  $args data to pass to Twig view
     * 
     * @return void
     */
    protected function view( string $view, array $args = [] )
    {
        try {
            /* ---------------------------- Views directories --------------------------- */
            $root_path = 'resources/views/';
            $pages_path = $root_path . '/pages';
            $partials_path = $root_path . '/partials';

            /* ------------------------ View loader configuration ----------------------- */
            $twig = new Environment(
                new FilesystemLoader([$pages_path, $partials_path]),
                ['auto_reload' => true]
            );

            $settings = $this->db()->table('settings')->select()->where('id', 1)->get()[0];
            $menu = $this->db()->table('policies')->select()->get();
            $downloads = $this->db()->table('downloads')->select()->orderBy('pos', 'asc')->get();


            /* -------------------- global filters available in view -------------------- */
            $twig->addFilter(new TwigFilter('cast_to_array', fn ($obj) => (array) $obj));


            /* ------------------- global functions available in view ------------------- */
            $twig->addFunction(new TwigFunction('_ENV', fn ($content) => $_ENV[$content]));
            $twig->addFunction(new TwigFunction('vardump', function ($content) {
                echo "<pre>";
                var_dump($content);
                echo "</pre>";
            }));
                        // Required to check availability of PDF file for an article
            $twig->addFunction(new TwigFunction('get_pdf', function ($content) {
                return array_search(
                    $_ENV['APP_ABBRV'] . '-' . $content .'.pdf',
                    array_diff(scandir($_SERVER['DOCUMENT_ROOT'].'/fulltext'), array('..', '.')), true
                );
            }));

            // Required to check availability of HTML file for an article
            $twig->addFunction(new TwigFunction('get_html', function ($content) {
                return array_search(
                    $_ENV['APP_ABBRV'] . '-' . $content .'.html',
                    array_diff(scandir($_SERVER['DOCUMENT_ROOT'].'/files_html'), array('..', '.')), true
                );
            }));


            /* ------------------- global variables available in view ------------------- */
            $twig->addGlobal('MENU', $menu);
            $twig->addGlobal('SETTINGS', $settings);
            $twig->addGlobal('DOWNLOADS', $downloads);

            /* ------------------------------- render view ------------------------------ */
            echo $twig->render($view . '.twig', $args);

        } catch (LoaderError | RuntimeError | SyntaxError $e) {
            echo '<pre>' . $e . '</pre>';
        }

        exit();
    }
}
