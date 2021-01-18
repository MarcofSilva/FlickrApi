<?php

namespace App\Http\Controllers;

use App\Flickr;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FlickrController extends Controller
{
    /**
     * get from flickr and list multiple photos
     */
    public function listPhotos(Request $request)
    {
        $flickr = new Flickr();

        $perPage = $request->input('per_page');
        $page = $request->input('page');
        $text = $request->input('text');

        if (is_null($perPage) and is_null($page)) {
            $res = $flickr->multiplePhotos(text:$text);
        } elseif (is_null($perPage) and !is_null($page)) {
            $res = $flickr->multiplePhotos(page:$page, text:$text);
        } else {
            $res = $flickr->multiplePhotos($perPage, $page, $text);
        }
        
        return response()->json($res);
    }

    /**
     * Get from flickr and list a single random photo
     */
    public function randomPhoto()
    {
        $flickr = new Flickr();
        return response()->json($flickr->randomPhoto());
    }
}
