<?php
require __DIR__ . '/../vendor/autoload.php';
 
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
 
use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use \LINE\LINEBot\SignatureValidator as SignatureValidator;
 
$pass_signature = true;
 
// set LINE channel_access_token and channel_secret
$channel_access_token = "ibNsWbFv5EM4AL0Kf1UF5aIR8wayvZD2ZnO+iH/VmOkAr6tEbBjuIk0r+AZFfdjEoRVRieFbRAewKEUfQiMNmLECu6H62bMFWJpdRIYkTtIOUl3BVUFUvWKy2oVwJ61yiArCPdaEfjy7T23oCtfraQdB04t89/1O/w1cDnyilFU=";
$channel_secret = "0ef1e1e00f82ef61d1fb330d3278acf0";
 
// inisiasi objek bot
$httpClient = new CurlHTTPClient($channel_access_token);
$bot = new LINEBot($httpClient, ['channelSecret' => $channel_secret]);
 
$app = AppFactory::create();
$app->setBasePath("/public");
 
$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello World!");
    return $response;
});
 
// buat route untuk webhook
$app->post('/webhook', function (Request $request, Response $response) use ($channel_secret, $bot, $httpClient, $pass_signature) {
    // get request body and line signature header
    $body = $request->getBody();
    $signature = $request->getHeaderLine('HTTP_X_LINE_SIGNATURE');
 
    // log body and signature
    file_put_contents('php://stderr', 'Body: ' . $body);
 
    if ($pass_signature === false) {
        // is LINE_SIGNATURE exists in request header?
        if (empty($signature)) {
            return $response->withStatus(400, 'Signature not set');
        }
 
        // is this request comes from LINE?
        if (!SignatureValidator::validateSignature($body, $channel_secret, $signature)) {
            return $response->withStatus(400, 'Invalid signature');
        }
    }


// kode aplikasi nanti di sini //

	// reply message
	$data = json_decode($body, true);
	if(is_array($data['events'])){
		foreach ($data['events'] as $event){
			if ($event['type'] == 'message'){

				if($event['message']['type'] == 'text'){

					if($event['message']['text'] == "help"){
						//panduan ea
						$result = $bot->replyText($event['replyToken'], 
"KALKULATOR:
• /add
menjumlahkan semua angka yang ditulis

• /substract
mengurangi angka pertama dengan angka kedua

NOTE: jika angka input lebih dari dua, hanya diambil 2 angka pertama

• /multiply
mengalikan semua angka yang ditulis

• /div
mengalikan dua angka dengan format
[hasil desimal], [hasil bulat], [sisa bagi]

NOTE: jika angka lebih dari dua, hanya diambil 2 angka pertama


");
					}

					else if(strpos($event['message']['text'], '/add') !== false){
						// add
						preg_match_all('!\d+!', $event['message']['text'], $num);
						$val = 0;
						foreach($num[0] as $i){
							$val += $i;
						}
						$result = $bot->replyText($event['replyToken'], "Hasil = $val");
					}

					else if(strpos($event['message']['text'], '/sub') !== false){
						// substract
						preg_match_all('!\d+!', $event['message']['text'], $num);
						$a = $num[0][0];
						$b = $num[0][1];
						$val = $a - $b;
						$result = $bot->replyText($event['replyToken'], "$a - $b = $val");
					}

					else if(strpos($event['message']['text'], '/multiply') !== false){
						// multiply
						preg_match_all('!\d+!', $event['message']['text'], $num);
						$val = 1;
						foreach($num[0] as $i){
							$val *= $i;
						}
						$result = $bot->replyText($event['replyToken'], "Hasil = $val");
					}

					else if(strpos($event['message']['text'], '/div') !== false){
						// divide
						preg_match_all('!\d+!', $event['message']['text'], $num);
						$a = $num[0][0]; $b = $num[0][1];
						$valdec = $a / $b;
						$valfloor = (int)($a / $b);
						$valremainder = $a % $b;
						$result = $bot->replyText($event['replyToken'], "$a / $b\nDesimal = $valdec\Hasil bagi (floor) = $valfloor\nSisa bagi = $valremainder");
					}

					else if(strpos($event['message']['text'], '/max') !== false){
						// max
						preg_match_all('!\d+!', $event['message']['text'], $num);
						$val = $num[0][0];
						foreach($num[0] as $i){
							if($i > $val){
								$val = $i;
							}
						}
						$result = $bot->replyText($event['replyToken'], "Terbesar: $val");
					}

					else if(strpos($event['message']['text'], '/min') !== false){
						// min
						preg_match_all('!\d+!', $event['message']['text'], $num);
						$val = $num[0][0];
						foreach($num[0] as $i){
							if($i < $val){
								$val = $i;
							}
						}
						$result = $bot->replyText($event['replyToken'], "Terkecil: $val");
					}

				}

				// buat game
				/*if($event['message']['type'] == 'text'){
	
					// variabel buat game
					$counter = 0;
					$a = rand(1,100);
					$b = rand(1,100);
					$c = $a + $b;
					$startgame = false;
					//$operatoridx = rand(1,3); // tambah kurang kali

					// command '/start'
					if($event['message']['text'] == "/start"){
						// mulai game
						$startgame = true;

						$startgame = false;
						$startText = new TextMessageBuilder("Game dimulai!");
						$soal = new TextMessageBuilder("$a + $b = ?\njawaban=$c");

						$startMultiMessage = new MultiMessageBuilder();
						$startMultiMessage->add($startText);
						$startMultiMessage->add($soal);

						$result = $bot->replyMessage($event['replyToken'],$startMultiMessage);
					}

					// command '/help'
					else if($event['message']['text'] == '/help'){
						// panduan main
						$result = $bot->replyText($event['replyToken'], "PANDUAN\n/start -> mulai permainan\n/help -> bantuan\n/quit -> keluar dari permainan");
					}
					
					// command '/quit'
					else if($event['message']['text'] == '/quit'){
						// dadah teks + stiker
						$startgame = false;
						$quitText = new TextMessageBuilder('Sampai berjumpa lagi!');
						$quitSticker = new StickerMessageBuilder(1,408);

						$quitMultiMessage = new MultiMessageBuilder();
						$quitMultiMessage->add($quitText);
						$quitMultiMessage->add($quitSticker);

						$result = $bot->replyMessage($event['replyToken'],$quitMultiMessage);
					}

					// dalam game
					if($startgame = true){
						global $a, $b, $c, $counter;
						if($event['message']['text'] == $c){
							global $a, $b, $c;
							$counter++; // tambah skor
							
							$correct = new TextMessageBuilder('Benar!!');
							$nextSoal = new TextMessageBuilder('Soal selanjutnya:');

							// ubah value $a dan $b
							$a = rand(1,100);
							$b = rand(1,100);
							$c = $a + $b;

							$soal = new TextMessageBuilder("$a + $b = ?");

							$nextMultiMessage = new MultiMessageBuilder();
							$nextMultiMessage->add($correct);
							$nextMultiMessage->add($nextSoal);
							$nextMultiMessage->add($soal);

							$result = $bot->replyMessage($event['replyToken'],$nextMultiMessage);

						} else{
							$startgame = false; // game kelar otomatis
							
							$wrong = new TextMessageBuilder("Salah... :(\nJawaban yang benar adalah $a + $b = $c");
							$stats = new TextMessageBuilder("Kamu telah benar $counter soal.");
							$replay = new TextMessageBuilder('Untuk bermain lagi, ketik /start !!');

							$wrongMultiMessage = new MultiMessageBuilder();
							$wrongMultiMessage->add($wrong);
							$wrongMultiMessage->add($stats);
							$wrongMultiMessage->add($replay);

							$result = $bot->replyMessage($event['replyToken'],$wrongMultiMessage);
						}
					}

					$response->getBody()->write(json_encode($result->getJSONDecodedBody()));
					return $response
						->withHeader('Content-Type', 'application/json')
						->withStatus($result->getHTTPStatus());
				}*/
			}
		}
	}

// kode aplikasi selesai di sini //


});

$app->run();
