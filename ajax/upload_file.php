<?php
// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'ajax_upload_file');

// ######################### REQUIRE BACK-END ############################
chdir('../');
require_once( realpath('include/global.php') );

// ########################### INIT VARIABLES ############################
ini_set("memory_limit", '512M');
ini_set("max_execution_time", '0');

$return = array(
              'done'    => null,
              'content' => '',
              'error'   => false,
          );

$uploadTempDir = $website -> config['Misc']['path'] . DS . $website -> config['Misc']['upload_directory'];

$imageTypes = array('gif', 'jpg', 'png');

// ########################### IDENTIFY USER #############################
loggedInOrReturn();
checkIfUserIsAdmin();
setDefaultForLoggedinUser();

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################
$website -> input -> clean_array_gpc('p', array(
                                              'filename'  => TYPE_NOHTML,
                                              'index'     => TYPE_UINT,
                                              'data'      => TYPE_NOCLEAN,
                                              'eof'       => TYPE_BOOL,
                                              'dest'      => TYPE_NOHTML,
                                              'filter'    => TYPE_NOHTML,
                                              'thumbnail' => TYPE_UINT,
                                          )
                                    );

if ( strlen($website -> GPC['data']) ) {
    $filePart = $uploadTempDir . DS . $website -> GPC['filename'] . '_' . $website -> GPC['index'] . '.part';
    file_put_contents($filePart, base64_decode($website -> GPC['data']), FILE_APPEND);

    $return['done'] = false;

    if ( $website -> GPC['eof'] == true ) {
        // Restore original file from uploaded chunks
        $outputName = $uploadTempDir . DS . $website -> GPC['filename'];
        $outputFile = fopen($outputName, "w");

        for ( $i = 0; $i <= $website -> GPC['index']; $i++ ) {
            $chunkFile = $uploadTempDir . DS . $website -> GPC['filename'] . '_' . $i . '.part';
            $chunkData = file_get_contents($chunkFile);

            fwrite($outputFile, $chunkData);
            unlink($chunkFile);
        }
        fclose($outputFile);

        // move restored file to new location
        $mediaManager = new MediaManager();
        $destPath = $mediaManager -> getDestinationMediaPath($website -> GPC['dest']);
        $destFile = $destPath . DS . $website -> GPC['filename'];
        $fileExt  = substr($destFile, -3);

        if ( $mediaManager -> canCurrentFileUploaded($website -> GPC['filename'], $website -> GPC['filter']) ) {
            if ( !is_file($destFile) ) {
                // save correct file
                $result = rename($outputName, $destFile);

                if ( $result == true ) {
                    // get new content from destination folder
                    //$return['content'] = $mediaManager -> getContentFromPath($website -> GPC['dest']);
                }
                else {
                    // return error-messages
                    $return['error']   = true;
                    $return['content'] = $website -> user_lang['admin']['media_manager_processing_file_error'];
                }

                // check whether a preview image should be created and whether the uploaded file could be an image
                if ( ($return['error'] == false) AND ($website -> GPC['thumbnail'] >= 1) AND in_array($fileExt, $imageTypes) ) {
                    $config = new Config();
                    $thumb  = new ImageProcessor();

                    $thumb -> setQuality( $config -> getConfigValue('image/thumbnail/quality') );
                    $thumb -> setThumbnailSizeX( $config -> getConfigValue('image/thumbnail/size') );

                    $thumbPrefix  = $config -> getConfigValue('image/thumbnail/prefix_small');
                    $thumnailFile = $destPath . DS . $thumbPrefix . $website -> GPC['filename'];

                    $thumb -> setSourceFile($destFile);
                    $thumb -> setDestinationFile($thumnailFile);
                    $thumb -> resizeJpgImage();

                    $thumbStatus = $thumb -> getErrorStatus();

                    if ( $thumbStatus['error'] == true ) {
                        $return['error']    = true;
                        $return['content'] .= (strlen($return['content']) ? '<br />' : '' ) . $thumbStatus['message'];
                    }

                    if ( $website -> GPC['thumbnail'] >= 2 ) {
                        // low-res image
                        $thumb -> setQuality( 10 );
                        $thumbPrefix = $config -> getConfigValue('image/thumbnail/prefix_low');
                        $lowResFile  = $destPath . DS . $thumbPrefix . $website -> GPC['filename'];

                        $thumb -> setDestinationFile($lowResFile);
                        $thumb -> resizeJpgImage();

                        $lowResStatus = $thumb -> getErrorStatus();

                        if ( $lowResStatus['error'] == true ) {
                            $return['error']    = true;
                            $return['content'] .= (strlen($return['content']) ? '<br />' : '' ) . $lowResStatus['message'];
                        }
                    }
                }

                if ( $return['error'] == false ) {
                    // get new content from destination folder
                    $return['content'] = $mediaManager -> getContentFromPath($website -> GPC['dest']);
                }
            }
            else {
                // destination-file already exists
                unlink($outputName);
                $return['error']   = true;
                $return['content'] = $website -> user_lang['admin']['media_manager_destination_file_exists'];
            }
        }
        else {
            // delete forbidden file
            unlink($outputName);
            $return['error']   = true;
            $return['content'] = $website -> user_lang['admin']['media_manager_wrong_file_to_be_upload'];
        }

        $return['done'] = true;
    }
}

echo json_encode($return);