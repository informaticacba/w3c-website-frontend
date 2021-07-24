<?php

declare(strict_types=1);

namespace App\Controller;

use App\Query\CraftCMS\GlobalNavigation;
use App\Query\W3C\PingQuery;
use App\Service\GraphQlDataFormatter;
use Strata\Data\Query\GraphQLQuery;
use Strata\Data\Query\QueryManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{

    /**
     * @Route("/debug", requirements={"route"=".+"}, defaults={"route"=""}, priority=-1)
     * @todo route priority is temporarily set to -1 as it's extremely greedy because of the {route} parameter.
     */
    public function debug(string $route, QueryManager $manager): Response
    {
        $manager->add('ping', new PingQuery());

//        $response = $w3CApi->getSpecifications();
//        $specifications = $w3CApi->getProvider()->decode($response);

        $nav = $manager->getCollection('navigation');
        dump($manager->getResponse('navigation'));

        return $this->render('debug/test.html.twig', [
            'title'             => 'Debug page',
            'navigation'        => $manager->getCollection('navigation'),
            'navigation_cached' => $manager->isHit('navigation'),
            'w3c_available'     => $manager->get('ping'),
//            'craft_available' => false, // $craftApi->ping(),
//            'specifications'  => [], //$specifications,
//            'specifications_cache_hit' => false, //$response->isHit(),
//            'route'           => '/' . $route,
        ]);
    }

    /**
     * @Route("/{route}", requirements={"route"=".+"}, defaults={"route"=""}, priority=-1)
     * @todo route priority is temporarily set to -1 as it's extremely greedy because of the {route} parameter.
     */
    //public function index(string $route, W3C $w3CApi, CraftCmsApi $craftApi, GraphQlQueryBuilder $queryBuilder): Response
    public function index(string $route, QueryManager $manager): Response
    {
        // Build queries
        $query = new GraphQLQuery('page', __DIR__ . '/../graphql/page.graphql');
        $query->addFragmentFromFile(__DIR__ . '/../graphql/fragments/landing-flexible-components.graphql')
              ->addVariable('slug', $route);
        $manager->add($query, 'craft');

        $query = new GraphQLQuery('localisation-data', __DIR__ . '/../graphql/global-navigation.graphql');
        $query->addVariable('slug', $route);
        $manager->add($query, 'craft');

        // Get data
        $pageContent = $manager->getItem('page', '[entry]');
        $pageLocalisations = $manager->getItem('localisation-data', '[entry]');

        // @todo replace this with Error 404 processing
        if ($pageContent === null) {
            throw new \Exception('No page data found!');
        }

        dump($manager);
        dump($pageContent);
        dump($manager->getResponse('page')->getContent());
        dump($pageLocalisations);

        return $this->render('debug/page.html.twig', [
            'page_translations'         => $pageLocalisations,
            'page_content_components'   => $pageContent,
            'route'                     => '/' . $route
        ]);
        
        return $this->render('debug/page.html.twig', [
            'page_translations'   => GraphQlDataFormatter::formatLocalisationDataForView($pageLocalisations),
            'page_content_components'   => GraphQlDataFormatter::formatLandingPageContentDataForView($pageContent),
            'route' => '/' . $route
        ]);
    }
}
