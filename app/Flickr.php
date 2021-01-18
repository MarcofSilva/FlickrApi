<?php

namespace App;

use App\Api;

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

        if (is_null($text) || strcmp('', trim($text)) === 0) {
            $method = 'flickr.photos.getRecent';
        } else {
            $method = 'flickr.photos.search';
            $parameters['text'] = $text;
        }
        
        $res = $this->request($method, $parameters);

        //cast total number of photos from string to int, for consistency
        $totalPhotos = (int) $res['photos']['total'];
        $res['photos']['total'] = $totalPhotos;

        //remove unwanted info
        $res['photos'] = array_diff_key($res['photos'], ['perpage' => '']);

        foreach ($res['photos']['photo'] as &$photo) {
            $photo = $this->updatePhotoInfo($photo, $photo['id'], $photo['secret']);
        }
        unset($photo);

        return $res;
    }
    
    /**
     * Get a single random photo from Flickr
     */
    public function randomPhoto()
    {
        $flickrResponse = $this->request('flickr.photos.getRecent', null);
        $randomPhoto = array_rand($flickrResponse['photos']['photo'], 1);
        
        $res = $flickrResponse['photos']['photo'][$randomPhoto];

        return $this->updatePhotoInfo($res, $res['id'], $res['secret']);
    }

    /**
     * Update the photos info.
     */
    public function updatePhotoInfo($photo, string $photoId, ?string $secretId = null)
    {
        //remove unwanted info
        $resPhoto = array_diff_key($photo, ['owner' => '', 'secret' => '', 'server' => '', 'farm' => '',  'ispublic' => '', 'isfriend' => '', 'isfamily' => '']);


        $info = $this->photoInfo($photoId, $secretId);
        $resPhoto['ownername'] = $info['photo']['owner']['realname'];
        $resPhoto['description'] = $info['photo']['description']['_content'];
        $resPhoto['url'] = $info['photo']['urls']['url'][0]['_content'];

        $size = $this->getPhotoSize($photoId);
        $resPhoto['height'] = $size['height'];
        $resPhoto['width'] = $size['width'];

        return $resPhoto;
    }

    /**
     * Get all info on a photo.
     */
    public function photoInfo(string $photoId, ?string $secretId = null)
    {
        $parameters['photo_id'] = $photoId;

        if (!is_null($secretId)) {
            $parameters['secret'] = $secretId;
        }

        return $this->request('flickr.photos.getInfo', $parameters);
    }

    /**
     * Get Average Height and Width of a photo
     */
    protected function getPhotoSize(string $photoId)
    {
        $parameters['photo_id'] = $photoId;
        $sizesResponse = $this->request('flickr.photos.getSizes', $parameters);
        
        $width = 0;
        $height = 0;
        $sizes = $sizesResponse['sizes']['size'];
        foreach ($sizes as $size) {
            $width += (int) $size['width'];
            $height += (int) $size['height'];
        }
        $width = intdiv($width, count($sizes));
        $height = intdiv($height, count($sizes));

        $size['height'] = $height;
        $size['width'] = $width;

        return $size;
    }
}