<?php
require_once( __DIR__ . '/../../../../../../src/bootstrap.php' );



$url = 'https://picasaweb.google.com/104478545363781605028/OnderlingToernooi2006';

if (preg_match('/picasaweb\.google\.com\/([0-9]+)\/([A-Za-z0-9]+)$/', $url, $matches))
{
    $userId = $matches[1];
    $albumName = $matches[2];

    $query = new Zend_Gdata_Photos_AlbumQuery();
    $query->setUser($userId);
    $query->setAlbumName($albumName);
    $query->setType('feed');
    $query->setKind('photo');
    $query->setImgMax( 1600 );
    $query->setThumbsize('144c');

    $service = new Zend_Gdata_Photos();

    try {
        $feed = $service->getAlbumFeed($query);
        var_dump(0);
        var_dump($feed);

        /** @var $entry Zend_Gdata_Photos_AlbumEntry */
        foreach( $feed as $entry )
        {
            $data = new stdClass();
            $data->id = $entry->getGphotoId()->getText();
            $data->title = $entry->getTitleValue();
            // timestamp is in *milliseconds* after epoch
            $ut = round( ( (float)$entry->getGphotoTimestamp()->getText() / 1000.0 ), 0 );
            //$data->datetime = new DateTime( $ut );
            $media = $entry->getMediaGroup();
            list( $thumb ) = $media->getThumbNail();
            list( $content ) = $media->getContent();
            $data->url = $content->getUrl();
            if( $thumb )
            {
                $data->thumb_url = $thumb->getUrl();
                $data->thumb_width = $thumb->getWidth();
                $data->thumb_height = $thumb->getHeight();
            }
            var_dump($data);
        }
    }
    catch (Zend_Gdata_App_Exception $e)
    {
        echo "Error: " . $e->getMessage();
    }
}
exit();

$client = null; //Zend_Gdata_ClientLogin::getHttpClient('webmaster@giveandgo.nl', 'resper93', Zend_Gdata_Photos::AUTH_SERVICE_NAME);
$service = new Zend_Gdata_Photos($client);
//$service = new Zend_Gdata_Photos(null, 'RichardHinkamp-BoltPicasa-0.1');

//try {
    $query = new Zend_Gdata_Photos_UserQuery();
//    $query->setKind( 'album' );
//    $query->setUser("webmaster@giveandgo.nl");
//    $albums = $service->getUserFeed(null, $query);
//    var_dump($albums);
//
//    foreach( $albums as $album )
//    {
//        var_dump($album->getGphotoNumPhotos()->text);
//        //Check number of photos before adding an album
//        if( (int)$album->getGphotoNumPhotos()->text > 0 )
//        {
//            $data = new stdClass();
//            $data->id = $album->getGphotoId()->getText();
//            $data->title = $album->getTitleValue();
//
//            // timestamp is in *milliseconds* after epoch
//            $data->ut = round( ( (float)$album->getGphotoTimestamp()->getText() / 1000.0 ), 0 );
//            //$data->date = new DateTime( $ut );
////            $media = $album->getMediaGroup();
////            list( $thumb ) = $media->getThumbNail();
////            if( $thumb )
////            {
////                $data->thumb_url = $thumb->getUrl();
////                $data->thumb_width = $thumb->getWidth();
////                $data->thumb_height = $thumb->getHeight();
////            }
//            var_dump($data);
//        }
//
//    }
//
//    var_dump($userFeed);
//} catch (Zend_Gdata_App_Exception $e) {
//    echo "Error: " . $e->getMessage();
//}
//exit();

