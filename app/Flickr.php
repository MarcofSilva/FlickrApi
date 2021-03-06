<?php

namespace App;

use App\Api;

const EXTRA_PARAMETERS = 'owner_name,url_o,description';

class Flickr
{

    public Api $api;

    public function __construct()
    {
        $this->api = new Api(env('FLICKR_KEY'));
    }

    public function request(string $method, ?array $parameters = null)
    {
        return $this->api->request($method, $parameters);
    }

    /**
     * Get multiple photos from Flickr
     */
    public function multiplePhotos($perPage = 20, $page = 1, ?string $text = null)
    {
        $parameters['per_page'] = $perPage;
        $parameters['page'] = $page;

        if (empty($text)) {
            $method = 'flickr.photos.getRecent';
        } else {
            $method = 'flickr.photos.search';
            $parameters['text'] = $text;
        }

        //parameter for requesting extra info
        $parameters['extras'] = EXTRA_PARAMETERS;

        $res = $this->request($method, $parameters);

        //cast total number of photos from string to int, for consistency
        $totalPhotos = (int) $res['photos']['total'];
        $res['photos']['total'] = $totalPhotos;

        //remove unwanted info
        $res['photos'] = array_diff_key($res['photos'], ['perpage' => '']);

        foreach ($res['photos']['photo'] as &$photo) {
            $photo = $this->updatePhotoInfo($photo);
        }
        unset($photo);

        return $res['photos'];
    }
    
    /**
     * Get a single random photo from Flickr
     */
    public function randomPhoto()
    {
        $parameters['extras'] = EXTRA_PARAMETERS;

        $flickrResponse = $this->request('flickr.photos.getRecent', $parameters);
        $randomPhoto = array_rand($flickrResponse['photos']['photo'], 1);
        
        $res = $flickrResponse['photos']['photo'][$randomPhoto];

        return $this->updatePhotoInfo($res);
    }

    /**
     * Update the photos info.
     */
    protected function updatePhotoInfo($photo)
    {
        //remove unwanted info
        $resPhoto = array_diff_key($photo, ['owner' => '', 'secret' => '', 'server' => '', 'farm' => '',  'ispublic' => '', 'isfriend' => '', 'isfamily' => '']);

        //correctly position description
        $resPhoto['description'] = $resPhoto['description']['_content'];


        /*TODO DELETE
        $info = $this->photoInfo($photoId, $secretId);
        $resPhoto['ownername'] = $info['photo']['owner']['realname'];
        $resPhoto['description'] = $info['photo']['description']['_content'];
        $resPhoto['url'] = $info['photo']['urls']['url'][0]['_content'];

        $size = $this->getPhotoSize($photoId);
        $resPhoto['height'] = $size['height'];
        $resPhoto['width'] = $size['width'];*/

        return $resPhoto;
    }

    /**
     * Get all info on a photo.
     */
    public function photoInfo(string $photoId, ?string $secretId = null)
    {
        $parameters['photo_id'] = $photoId;

        if (!empty($secretId)) {
            $parameters['secret'] = $secretId;
        }

        return $this->request('flickr.photos.getInfo', $parameters);
    }
}