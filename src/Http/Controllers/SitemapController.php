<?php

namespace Platform\Portal\Http\Controllers;

use Illuminate\Http\Response;
use Platform\Portal\Repositories\PortalPageRepository;

class SitemapController extends Controller
{
    /**
     * Display dynamic sitemap.xml including active portal and CMS pages.
     */
    public function index(PortalPageRepository $portalPageRepository): Response
    {
        $channel = core()->getCurrentChannel();

        $pages = $portalPageRepository->getModel()
            ->where('status', 1)
            ->when($channel, fn ($q) => $q->where('channel_id', $channel->id))
            ->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // 1. Home page entry
        $xml .= '<url>';
        $xml .= '<loc>'.htmlspecialchars(portal_home_url()).'</loc>';
        $xml .= '<priority>1.0</priority>';
        $xml .= '<changefreq>daily</changefreq>';
        $xml .= '</url>';

        // 2. Dynamic portal pages
        foreach ($pages as $page) {
            if ($page->is_home_page) {
                continue;
            }

            $loc = route('portal.page.show', $page->slug);
            $lastmod = $page->updated_at?->toAtomString() ?? now()->toAtomString();

            $xml .= '<url>';
            $xml .= '<loc>'.htmlspecialchars($loc).'</loc>';
            $xml .= '<lastmod>'.$lastmod.'</lastmod>';
            $xml .= '<priority>0.8</priority>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '</url>';
        }

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
