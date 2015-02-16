<?php

namespace BoltPicasa\Console;

use Bolt\Nut\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncPicasa extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('boltpicasa:sync')
            ->setDescription('Sync Picasa photo albums');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $albumContentId = $this->app['config']->get('general/bolt_picasa/album_slug');
        $photoContentId = $this->app['config']->get('general/bolt_picasa/photo_slug');

        $albums = $this->app['storage']->getContent($albumContentId, array('sync' => 1));
        if (count($albums) > 0) {
            $output->writeln('To sync ' . count($albums) . ' albums from content id ' . $albumContentId);
        }
        foreach($albums as $album)
        {
            /** @var \Bolt\Content $album */
            $output->writeln('Album: ' . $album->get('title'));
            if (preg_match('/picasaweb\.google\.com\/([0-9]+)\/([A-Za-z0-9]+)/', $album->get('url'), $matches))
            {
                $userId = $matches[1];
                $albumName = $matches[2];
                $output->writeln('User: ' . $userId . ' Album: ' . $albumName);

                $query = new \Zend_Gdata_Photos_AlbumQuery();
                $query->setUser($userId);
                $query->setAlbumName($albumName);
                $query->setType('feed');
                $query->setKind('photo');
                $query->setImgMax( 1600 );
                $query->setThumbsize('144c'); // does not work for icon
                if (preg_match('/authkey=([A-Za-z0-9]+)/', $album->get('url'), $m)) {
                    $query->setParam('authkey', $m[1]);
                }

                $service = new \Zend_Gdata_Photos();

                try {
                    /** @var \Zend_Gdata_Photos_AlbumFeed $feed */
                    $feed = $service->getAlbumFeed($query);
                    $album->values['thumb_url'] = $feed->getIcon()->getText();
                    $album->values['title'] = $feed->getTitleValue();
                    $album->values['slug'] = makeSlug($feed->getTitleValue());
                    $album->values['status'] = 'published';
                    $ut = round(((float)$feed->getGphotoTimestamp()->getText() / 1000.0 ), 0 );
                    $album->values['datepublish'] = date("Y-m-d H:i:s", $ut);

                    $p = array();
                    $curPhotos = $this->app['storage']->getContent($photoContentId,array('limit'=>9999),$p,array('album_id'=>$album->get('id')));
                    foreach($curPhotos as $curPhoto) {
                        $this->app['storage']->deleteContent($photoContentId, $curPhoto->get('id'));
                    }

                    /** @var $entry \Zend_Gdata_Photos_AlbumEntry */
                    foreach( $feed as $entry )
                    {
                        $photo = new \Bolt\Content($this->app, $photoContentId, array());
                        $photo->values['album_id'] = $album->get('id');
                        $photo->values['title'] = $feed->getTitleValue();
                        // timestamp is in *milliseconds* after epoch
                        $ut = round( ( (float)$entry->getGphotoTimestamp()->getText() / 1000.0 ), 0 );
                        $photo->values['datepublish'] = date("Y-m-d H:i:s", $ut);
                        $media = $entry->getMediaGroup();
                        list( $thumb ) = $media->getThumbNail();
                        list( $content ) = $media->getContent();
                        $photo->values['url'] = $content->getUrl();
                        if( $thumb )
                        {
                            $photo->values['thumb_url'] = $thumb->getUrl();
                        }
                        $this->app['storage']->saveContent($photo);
                    }
                }
                catch (\Zend_Gdata_App_Exception $e)
                {
                    echo "Error: " . $e->getMessage();
                }
            }
            else
            {
                $output->writeln("Invalid Picasa URL");
            }
            $album->values['sync'] = 0;
            $this->app['storage']->saveContent($album);
        }
    }
}
