<pre><?php
/**
 * Used to update the legacy database to the new one
 * Created by PhpStorm.
 * User: Vittorio
 * Date: 12/01/2017
 * Time: 11:30.
 */
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

require_once '../../vendor/autoload.php';

use Lib\MusicClasses\Album;

include_once '../php-lib/MusicClasses/Album.php';

echo '<h1>[@] Starting migration...</h1>', PHP_EOL;

foreach (Album::getAllAlbums() as $album) {
    echo '<h2>', $album->getTitle(), '</h2><ul>';
    foreach ($album->getLegacySongs() as $track_no => $song) {
        if ($song->getTrackNo() == 0) {
            $song->setTrackNo($track_no + 1);
        }

        $song->save();
        $song->addArtist($album->getLegacyArtist()->getId());
        echo '<li>', json_encode($song), '</li>';
    }
    echo '</ul>';
}

echo PHP_EOL, '<h1>[@] Migration Complete.</h1>';
