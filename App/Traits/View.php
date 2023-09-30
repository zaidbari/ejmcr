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
            $theme_path = $root_path . $_ENV['JOURNAL_ABBREV'];
            $pages_path = $theme_path . '/pages';
            $partials_path = $theme_path . '/partials';

            
            $settings = $this->db()->table('settings')->select()->where('id', 1)->one() ?? null;
            if (!$settings) {
                $this->db()->table('settings')->insert(['id' => 1])->execute();
                $settings = $this->db()->table('settings')->select()->where('id', 1)->one();
            }

            $featured_article = $this->db()->table('featuredarticle')->select()->one() ?? null;
            if (!$featured_article) {
                $this->db()->table('featuredarticle')->insert(['id' => 1])->execute();
                $featured_article = $this->db()->table('featuredarticle')->select()->where('id', 1)->one();
            }

            
            /* ------------------------ View loader configuration ----------------------- */
            $twig = new Environment(
                new FilesystemLoader([$pages_path, $partials_path, $root_path]),
                ['auto_reload' => true]
            );

            $pages_count = $this->db()->table('pages')->select()->where('isPublished', true)->count();
            $menu = $this->db()->table('pages')->select()->where('isPublished', 1)->get();
            // $downloads = $this->db()->table('downloads')->select()->orderBy('pos', 'asc')->get();

            

            /* -------------------- global filters available in view -------------------- */
            $twig->addFilter(new TwigFilter('cast_to_array', fn ($obj) => (array) $obj));


            /* ------------------- global functions available in view ------------------- */

            // truncate string to `length` characters
            $twig->addFunction(new TwigFunction('_TRUNC', fn ($content) => substr($content, 0, 150) . '...'));
            $twig->addFunction(new TwigFunction('decode', fn ($content) => html_entity_decode($content)));
            $twig->addFunction(new TwigFunction('_GET', fn ($content) => $_GET[$content]));
            $twig->addFunction(new TwigFunction('_ENV', fn ($content) => $_ENV[$content]));
            $twig->addFunction(new TwigFunction('vardump', function ($content) {
                echo "<pre>";
                var_dump($content);
                echo "</pre>";
            }));


            $twig->addFunction(new TwigFunction('has', function ($data) {
                return $_SESSION[$data] ?? false;
            }));
            $twig->addFunction(new TwigFunction('flash', function () {
                $msg = $_SESSION['flash'];
                unset($_SESSION['error']); 
                unset($_SESSION['success']);
                unset($_SESSION['flash']);
                return $msg;
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
            $twig->addGlobal('PAGE_COUNT', $pages_count);
            $twig->addGlobal('SETTINGS', $settings);
            $twig->addGlobal('MENU', $menu);

            // $twig->addGlobal('DOWNLOADS', $downloads);

            /* ------------------------------- render view ------------------------------ */
            echo $twig->render($view . '.twig', $args);

        } catch (LoaderError | RuntimeError | SyntaxError $e) {
            echo '<pre>' . $e . '</pre>';
        }

        exit();
    }
}
