<?php
/**
 * Created by PhpStorm.
 * User: dkuzmin
 * Date: 05.10.16
 */

namespace controllers;

class galleryController
{
    protected $ci;

    /**
     * galleryController constructor.
     * getting dependency container
     * @param $ci
     */
    public function __construct($ci) {
        $this->ci = $ci;
    }


    /**
     * show start page
     */
    public function showPage($request, $response, $args){
        return $this->ci->renderer->render($response, 'index.phtml');
    }


    /**
     * processing search request
     */
    public function searchImage($request, $response, $args){
        $parsedBody = $request->getParsedBody();
        $queryRequest = preg_replace('![^\w\d\s]*!', '', $parsedBody['query']);
        $numRequest = $this->ci->settings['numberOfImages'];
        $saveFolder =  $this->ci->settings['public_folder'] . 'images/';

        try{
            $imageSearcher = new \googleImageSearcher(
                $this->ci->settings['googleKeys']['devApiKey'],
                $this->ci->settings['googleKeys']['customSearchKey']
            );
            $storeManager = new \imageStoreManager(
                $saveFolder . \imageStoreManager::sanitizeString($queryRequest) . '/',
                $this->ci->settings['downloadTimeout']
            );
        }catch (\Exception $e){
            $this->ci->logger->info('Fatal error while initialization. ' . $e->getMessage());
            return $response->withJson(array('error' => $e->getMessage()), 500);
        }

        $imageSearcher->setQuery($queryRequest);
        $storeManager->emptyStorage();

        try{
            $imagesDataArray = $imageSearcher->getAll($numRequest);
        }catch (\Exception $e){
            $this->ci->logger->info('Fatal error while search. ' . $e->getMessage());
            return $response->withJson(array('error' => $e->getMessage()), 500);
        }


        foreach ($imagesDataArray as $key=>$imageData){
            try{
                $storeManager->storeImage(
                    $imageData['link'],
                    \imageStoreManager::sanitizeString( $imageData['snippet'] ) . '.jpg'
                );
            }catch (\Exception $e ){
                $this->ci->logger->info('Error with image saving. ' . $e->getMessage());
            }
        }


        $imagesURLs = array();
        foreach ($storeManager->getSavedImages() as $image){
            $imagesURLs[] = 'images/' . \imageStoreManager::sanitizeString($queryRequest) . '/' . $image;
        }

        return $response->withJson(array('images' => $imagesURLs));
    }


}