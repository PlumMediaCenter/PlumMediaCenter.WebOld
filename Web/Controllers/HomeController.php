<?php

class HomeController extends Controller {

    /**
     * Default action
     */
    function Index() {
        return RedirectToAction("Browse");
    }

    function Browse() {
        include_once(basePath() . '/Models/Home/BrowseModel.php');
        $m = new \Models\Home\BrowseModel();
        $m->process();
        return View($m);
    }

    function Genre() {
        return View();
    }

    function TestApi() {
        return View();
    }

    function Search($q) {
        include_once(basePath() . '/code/Library.class.php');
        $videos = Library::SearchByTitle($q);
        return View((object) ['videos' => $videos], 'Index');
    }

    function VideoInfo($videoId) {
        include_once(basePath() . '/Code/Video.class.php');
        $video = Video::GetVideo($videoId);
        //if this is a tv show, load the episodes from the database
        if ($video->getMediaType() == Enumerations\MediaType::TvShow) {
            $video->loadEpisodesFromDatabase();
        }
        return View((object) ['video' => $video]);
    }

    function Play($videoId) {
        include_once(basePath() . '/Models/PlayModel.php');
        $model = new PlayModel();
        $model->init($videoId);
        return View($model);
    }

    function PlayPlaylist($playlistName) {
        include_once(basePath() . '/Models/PlayModel.php');
        $model = new PlayModel();
        $model->initPlaylist($playlistName);
    }

    function GenerateNewLibrary() {
        include_once(dirname(__file__) . "/../Code/NewLibrary.class.php");
        $l = new NewLibrary();
        $l->generateLibrary();
    }

    function poster() {
        include_once(dirname(__file__) . "/../Code/lib/PHPImageWorkshop/ImageWorkshop.php");
        $text = "This is a movie title that is really super long";
        $fontPath = dirname(__FILE__) . '/../Content/Fonts/Liberation-Mono/LiberationMono-Regular.ttf';
        $fontColor = "000000";
        $textRotation = 0;
        $borderWidth = 25;
        $backgroundColor = "FFFFFF";
        $posterWidth = 500;
        $posterHeight = 750;
        $maxCharactersPerRow = 20;
        $fontSize = $posterWidth / $maxCharactersPerRow;

        //create the main poster 
        $document = \PHPImageWorkshop\ImageWorkshop::initVirginLayer($posterWidth, $posterHeight);

        $textItems = array();
        $textItems[] = (object) array('x' => null, 'text' => $text, 'layer' => null);
        $textIsReady = false;
        //walk through the text provided and try to fit it all on the poster. This may require splitting the text up into chunks and drawing
        //it in multiple lines.
        while ($textIsReady === false) {
            $textItemCount = count($textItems);
            $finishedCount = 0;
            foreach ($textItems as $key => $textItem) {
                //if the layer has not been created yet, try to create it
                if ($textItem->layer === null) {
                    $thisTextItemText = $textItem->text;
                    $textLength = strlen($thisTextItemText);

                    $twoCharLayer = \PHPImageWorkshop\ImageWorkshop::initTextLayer('AA', $fontPath, $fontSize, $fontColor, $textRotation, $backgroundColor);
                    $singleCharWidth = $twoCharLayer->getWidth() / 2;
                    $layerWidth = $singleCharWidth * $textLength;
                    $xDiff = ($posterWidth - ($borderWidth * 2)) - $layerWidth;

                    //if the text layer wont fit on one line, split it into chunks.
                    if ($xDiff < 0) {
                        //split the text into two equal pieces.
                        $firstHalfEndingChar = floor($textLength / 2);
                        $firstHalfText = substr($thisTextItemText, 0, $firstHalfEndingChar);
                        $secondHalfText = substr($thisTextItemText, $firstHalfEndingChar, $textLength);
                        $textItem->text = $firstHalfText;
                        $secondHalfTextItem = (object) array('x' => null, 'text' => $secondHalfText, 'layer' => null);
                        //insert this item right next to its first half
                        array_splice($textItems, $key + 1, 0, array($secondHalfTextItem));
                        //exit the for loop and start it over.
                        break;
                    } else {
                        //this text item is going to fit onto the poster. Keep it and move on to the next item
                        $textItem->layer = \PHPImageWorkshop\ImageWorkshop::initTextLayer($thisTextItemText, $fontPath, $fontSize, $fontColor, $textRotation, $backgroundColor);
                        //calculate the x for the starting position of the text so that it is centered
                        $textItem->x = ($posterWidth / 2) - ($layerWidth / 2);
                        //this layer is finished. 
                        $finishedCount += 1;
                        //($outerBoxWidth / 2) - ($boxWidth / 2)
                    }
                } else {
                    //this layer is finished
                    $finishedCount += 1;
                }
            }
            //the number of finished items equals the number of expected finished items. Exit the while loop
            if ($finishedCount === $textItemCount) {
                $textIsReady = true;
            }
        }

        $layerIdx = 1;
        $rowCount = count($textItems);

        $middleRowIndex = floor($rowCount / 2);
        $yMargin = 5;
        $idx = 0;
        //add each layer to the document
        foreach ($textItems as $textItem) {
            $textLayer = $textItem->layer;
            $textLayerHeight = $textLayer->getHeight();
            //calculate the y position for this row of text
            $posterCenter = ( $posterHeight / 2) - ($textLayerHeight / 2);
            $yFactor = $idx - $middleRowIndex;

            $yPos = $posterCenter + ($yFactor * ($textLayerHeight + $yMargin ));

            //add the text layer to the document
            $document->addLayer($layerIdx++, $textItem->layer, $textItem->x, $yPos);
            $idx++;
        }


        //create the border
        $borderLayer = \PHPImageWorkshop\ImageWorkshop::initVirginLayer($document->getWidth(), $document->getHeight()); // This layer will have the width and height of the document
        $borderColor = "000000";
        $horizontalBorderLayer = \PHPImageWorkshop\ImageWorkshop::initVirginLayer($document->getWidth(), $borderWidth, $borderColor);
        $verticalBorderLayer = \PHPImageWorkshop\ImageWorkshop::initVirginLayer($borderWidth, $document->getHeight(), $borderColor);
        $borderLayer->addLayer(1, $horizontalBorderLayer, 0, 0);
        $borderLayer->addLayer(2, $horizontalBorderLayer, 0, 0, 'LB');
        $borderLayer->addLayer(3, $verticalBorderLayer, 0, 0);
        $borderLayer->addLayer(4, $verticalBorderLayer, 0, 0, 'RT');
        $document->addLayer(2, $borderLayer);
        $dirPath = "C:/";
        $filename = "image.jpg";
        $createFolders = true;
        $backgroundColor = "FFFFFF";
        $imageQuality = 100;
        $document->save($dirPath, $filename, $createFolders, $backgroundColor, $imageQuality);
    }

}
