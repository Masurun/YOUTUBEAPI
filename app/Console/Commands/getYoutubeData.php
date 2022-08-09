<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google_Client;
use Google_Service_YouTube;
use Google_Service_Exception;
use Google_Exception;

class getYoutubeData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:get_youtube_data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $apikey = env('YOUTUBE_API_KEY');
        $client = new Google_Client();
        $client->setApplicationName("youtube-api-test");
        $client->setDeveloperKey($apikey);
        $youtube = new Google_Service_YouTube($client);

        //動画の取得
        // $params['channelId'] = '';
        // $params['type'] = 'video';
        // $params['maxResults'] = 10;
        // $params['order'] = 'date';
        // try {
        //     $searchResponse = $youtube->search->listSearch('snippet', $params);
        // } catch (Google_Service_Exception $e) {
        //     echo htmlspecialchars($e->getMessage());
        //     exit;
        // } catch (Google_Exception $e) {
        //     echo htmlspecialchars($e->getMessage());
        //     exit;
        // }
        // foreach ($searchResponse['items'] as $search_result) {
        //     $videos[] = $search_result;
        // }

        $res = fopen(env('LOCAL_DIR'), 'w');
        $header = ["日付", "ユーザー名", "コメント", "返信数", "いいね"];
        fputcsv($res, $header);
        $nextPageToken = NULL;
        while (true) {
            $comments = $youtube->commentThreads->listCommentThreads('snippet,replies', array(
                'videoId' => 'qrFdhYGcfw0',
                'textFormat' => 'plainText',
                'pageToken' => $nextPageToken,
                'maxResults' => 100,
            ));
            for ($i = 0; $i < 100; $i++) {
                $arr = array();
                if (isset($comments[$i])) {
                    $arr[] = $comments[$i]['snippet']['topLevelComment']['snippet']['updatedAt'];
                    $arr[] = $comments[$i]['snippet']['topLevelComment']['snippet']['authorDisplayName'];
                    $arr[] = $comments[$i]['snippet']['topLevelComment']['snippet']['textDisplay'];
                    $arr[] = $comments[$i]['snippet']['topLevelComment']['snippet']['totalReplyCount'];
                    $arr[] = $comments[$i]['snippet']['topLevelComment']['snippet']['likeCount'];
                }
                fputcsv($res, $arr);
            }
            $nextPageToken = $comments['nextPageToken'];
            if ($nextPageToken == NULL) {
                break;
            }
        }
        //文字化け対策　未実装
        // mb_convert_variables('SJIS', 'UTF-8', $res);
        fclose($res);
    }
}
