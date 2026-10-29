<?php

namespace Platform\Portal\Http\Controllers;

use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Display sitemap.xml.
     */
    public function index(): Response
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        $xml .= '<url><loc>'.url('/').'</loc><priority>1.0</priority></url>';
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    /**
     * Display robots.txt.
     */
    public function robots(): Response
    {
        $robots = "User-agent: *\nDisallow: /admin/\nSitemap: ".url('/sitemap.xml');

        return response($robots, 200, ['Content-Type' => 'text/plain']);
    }
}
