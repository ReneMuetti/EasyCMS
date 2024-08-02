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

// ########################### IDENTIFY USER #############################
loggedInOrReturn();
checkIfUserIsAdmin();
setDefaultForLoggedinUser();

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################
$website -> input -> clean_array_gpc('p', array(
                                              'filename' => TYPE_NOHTML,
                                              'index'    => TYPE_UINT,
                                              'data'     => TYPE_NOCLEAN,
                                              'eof'      => TYPE_BOOL,
                                              'dest'     => TYPE_NOHTML,
                                              'filter'   => TYPE_NOHTML,
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

        if ( $mediaManager -> canCurrentFileUploaded($website -> GPC['filename'], $website -> GPC['filter']) ) {
            if ( !is_file($destPath . DS . $website -> GPC['filename']) ) {
                // save correct file
                $result = rename($outputName, $destPath . DS . $website -> GPC['filename']);

                if ( $result == true ) {
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