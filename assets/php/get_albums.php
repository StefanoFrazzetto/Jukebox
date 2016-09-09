<?php
require 'dbconnect.php';
require 'get_cover.php';

$return['code'] = 0;

$type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING);

$page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT);
$show_x = filter_input(INPUT_GET, 'x', FILTER_SANITIZE_NUMBER_INT);
$show_y = filter_input(INPUT_GET, 'y', FILTER_SANITIZE_NUMBER_INT);
$show = $show_x * $show_y;
recheck:
$paginate = 'LIMIT ' . ($page - 1) * $show . ', ' . $show;

$order_by = filter_input(INPUT_GET, 'orderBy', FILTER_SANITIZE_NUMBER_INT);
switch ($order_by) {
    case '1':
        $order_by = 'artist, title';
        break;
    case '2':
        $order_by = 'title, artist';
        break;
    case '3':
        $order_by = 'hits DESC, artist, title';
        break;
    case '4':
        $order_by = 'last_played DESC, artist, title';
        break;
    case '5':
        $order_by = 'id DESC';
        break;
    default:
        $order_by = 'artist, title';
        break;
}

//This will handle the various requests and query the database
switch ($type) {
    case 'all':
        //SELECT COUNT(*) FROM table_name
        $totals = $mysqli->query("SELECT COUNT(*) FROM $albums")->fetch_assoc()['COUNT(*)'];
        $results = $mysqli->query("SELECT * FROM $albums ORDER BY $order_by $paginate");
        break;
    case 'alphabet':
        $alpha = filter_input(INPUT_GET, 'alphabet', FILTER_SANITIZE_STRING);

        if ($alpha) {
            $where = "WHERE artist LIKE '$alpha%'";
        } else {
            $where = "WHERE artist regexp '^[0-9]+'";
        }

        $totals = $mysqli->query("SELECT COUNT(*) FROM $albums $where")->fetch_assoc()['COUNT(*)'];
        $results = $mysqli->query("SELECT * FROM $albums $where ORDER BY $order_by $paginate");
        break;

    case 'search':
        $search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING);

        $search_field = filter_input(INPUT_GET, 'searchField', FILTER_SANITIZE_STRING);

        $where = "WHERE $search_field LIKE '%$search%'";

        $totals = $mysqli->query("SELECT COUNT(*) FROM $albums $where")->fetch_assoc()['COUNT(*)'];
        $results = $mysqli->query("SELECT * FROM $albums $where ORDER BY $order_by $paginate");
        break;

    default:
        echo '<strong>BAD REQUEST ' . $type . ' </strong><br/><pre> ' . print_r($_GET) . '</pre>';
        break;
}

//From now on we will elaborate the results taken from the database in order to show them

if (!$results->num_rows & $totals != 0) {
    $page --;
    goto recheck;
}
if (!$results->num_rows) {
    $return['code'] = 1;
} else {
    $return['total'] = $totals;

    if ($page == 1) {
        $return['isFirstPage'] = TRUE;
    } else {
        $return['isFirstPage'] = FALSE;
    }

    if ($page * $show >= $totals) {
        $return['isLastPage'] = TRUE;
    } else {
        $return['isLastPage'] = FALSE;
    }

    for ($key = 1; $key <= $show; $key ++) {
        if ($key == 1) {
            echo '<tr>';
        }
        if ($album = $results->fetch_object()) {
            $id = $album->id;
            $artist = $album->artist;
            $title = $album->title;

            $pictureURL = get_cover($id, 1);
            ?>
            <td> 
                <div class="album" id="<?php echo $id ?>">
                    <div class="moar"><i class="fa fa-play"></i></div>
                    <img src="<?php echo $pictureURL ?>" />
                    <div class="albumDetails">
                        <p class="albumArtist"><?php echo mb_strimwidth($artist, 0, 27, "...") ?><p>
                        
			<p class="albumTitle"><?php echo mb_strimwidth($title, 0, 27, "...") ?></p>

                    </div>
                </div>
            </td>
            <?php
        } else {
            echo '<td><div class="album filler"></div></td>';
        }
        if ((($key) % ($show_x) == 0) && $key != 1) {
            if ($key == $show) {
                echo '</tr>';
            } else {
                echo '</tr><tr>';
            }
        }
    }
}

echo "<!--", json_encode($return), "-->";
