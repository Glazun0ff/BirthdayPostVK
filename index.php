<?php

	require "config.php";

	$api_version = '5.95';
	$vk = new Vk($token);

	// День рождение начало
	$datedr = date("j.n"); // Текущая дата (день.месяц без 0)
	$date_len = strlen($datedr); 

	$page = 0;
	$limit = 1000;
	$users = array();

	do {
	  $offset = $page * $limit;
	  //Получаем список пользователей
	  //$members = json_decode(file_get_contents("https://api.vk.com/method/groups.getMembers?group_id={$group_id}&offset={$offset}&count={$limit}&fields=bdate,photo_100&access_token={$access_token}&v={$api_version}"), true);
	  $members = json_decode(file_get_contents("https://api.vk.com/method/groups.getMembers?group_id={$group_id}&offset={$offset}&count={$limit}&fields=bdate&access_token={$token}&v={$api_version}"), true);
	  //Спим
	  //sleep(2);
	  
	  for($i = 0; $i < count($members['response']['items']); $i++) {
		  $users []= $user; // добавляем юзера к юзерам
		  // Отбираем пользователей у кого сегодня др
		  $bdates = explode(",", $members['response']["items"][$i]["bdate"]);
		  foreach ($bdates as $bdate) {
			  
			  if (substr($bdate, 0, $date_len) == $datedr && ((strlen ($bdate) == $date_len) || substr($bdate, $date_len, 1) == ".")) { // Вычисляем дату др
				  $birthday_subscribe_id = $members['response']["items"][$i]["id"];
				  $birthday_subscribe_firstname = $members['response']["items"][$i]["first_name"];
				  $birthday_subscribe_lastname = $members['response']["items"][$i]["last_name"];
				  //print_r("@".$birthday_subscribe_id." (".$birthday_subscribe_firstname." ".$birthday_subscribe_lastname.")"."<br>");
				  $birthday_subscribes = $birthday_subscribes."@id".$birthday_subscribe_id." (".$birthday_subscribe_firstname." ".$birthday_subscribe_lastname."), ";
				  }                 
			  }      
		  }    
		//Увеличиваем страницу
		$page++;
		} while ($members['response']['count'] > $offset + $limit );
	$birthday_subscribes = substr($birthday_subscribes,0,strlen($birthday_subscribes)-2); // отсекам 2 знака в конце строки
	//print_r($birthday_subscribes);

	//foreach ($users as $n => $user) // ходим по юзерам
	  //if(@$user['deactivated']) // и забаненных
		//unset($users[$n]); // удаляем

	$image = ('pic/dr.jpg');
	$upload_server = $vk->photosGetWallUploadServer($group_id);
	$upload = $vk->uploadFile($upload_server['upload_url'], $image);
	$save = $vk->photosSaveWallPhoto([
			'group_id' => $group_id,
			'photo' => $upload['photo'],
			'server' => $upload['server'],
			'hash' => $upload['hash']
		]);
	$attachments = sprintf('photo%s_%s', $save[0]['owner_id'], $save[0]['id']);

	/*$image = ('pic/dr.gif');
	$upload_server = $vk->docsGetWallUploadServer($group_id);
	$upload = $vk->uploadFile($upload_server['upload_url'], $image);
	$save = $vk->docsSave([
			'group_id' => $group_id,
			'file' => $upload['file']
		]);
	$attachments = $save;
	echo "$attachments";*/

	$post = $vk->wallPost([
		'owner_id' => "-$group_id",
		'from_group' => 1,
		'message' => "🎉Именинников пост🎉 \r\nДорогие подписчики! Каждый день мы публикуем имена наших именинников 😊 Каждому из вас мы дарим купон со скидкой на обучение в автошколе «Курьер» 🎁"."\r\n\r\n".
					 "#cднёмрождения #happybirthday \r\nПоздравляем наших подписчиков с днем рождения!"."\r\n\r\n".
					 "$birthday_subscribes"."\r\n\r\n".
					 "Получить купон очень просто! 😉 \r\nПокажите паспорт в автошколе «Курьер» и получите скидку в 500 рублей 💰"."\r\n\r\n".
					 "♻ Купон действует неделю до дня рождения и неделю после!"."\r\n\r\n".
					 "С Днем рождения, друзья!🎈🎈🎈"."\r\n\r\n".
					 "__________"."\r\n".
					 "🚗Автошкола «Курьер» Таганрог"."\r\n".
					 "📍Поляковское шоссе, 18А"."\r\n".
					 "📱тел. 8 (989) 5-200-100"."\r\n".
					 "📍пер. Комсомольский 14, 2-й этаж"."\r\n".
					 "📱тел. 8 (989) 6-300-100"."\r\n".
					 "📌сайт: taxi-kurier.ru/autoschool"."\r\n".
					 "✉задать вопрос: vk.me/avtoshkolakurier"."\r\n".
					 "📘FB: facebook.com/avtoshkolakurier"."\r\n".
					 "📙OK: ok.ru/taxi.kurier"."\r\n".
					 "📕INST: instagram.com/avtoshkola_kurier_taganrog"."\r\n".
					 "__________"."\r\n".
					 "#таганрог #курьер #автошкола #автошколакурьер #права #получениеправ #заруль #обучение #поехали",
		'attachments' => $attachments
	]);

	//
	class Vk
		{
		private $token;
		private $v = '5.92';

		public function __construct($token)
		{
			$this->token = $token;
		}

		public function wallPost($data)
		{
			return $this->request('wall.post', $data);
		}

		public function photosGetWallUploadServer($group_id)
		{
			$params = [
				'group_id' => $group_id,
			];
			return $this->request('photos.getWallUploadServer', $params);
		}
		
		public function docsGetWallUploadServer($group_id)
		{
			$params = [
				'group_id' => $group_id,
			];
			return $this->request('docs.getWallUploadServer', $params);
		}

		/**
		 * @param $params [user_id, group_id, photo, server, hash]
		 * @return mixed
		 * @throws \Exception
		 */
		
		public function photosSaveWallPhoto($params)
		{
			return $this->request('photos.saveWallPhoto', $params);
		}
		
		public function docsSave($params)
		{
			return $this->request('docs.save', $params);
		}

		public function uploadFile($url, $path)
		{
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POST, true);

			if (class_exists('\CURLFile')) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, ['file1' => new \CURLFile($path)]);
			} else {
				curl_setopt($ch, CURLOPT_POSTFIELDS, ['file1' => "@$path"]);
			}

			$data = curl_exec($ch);
			curl_close($ch);
			return json_decode($data, true);
		}

		private function request($method, array $params)
		{
			$params['v'] = $this->v;

			$ch = curl_init('https://api.vk.com/method/' . $method . '?access_token=' . $this->token);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			$data = curl_exec($ch);
			curl_close($ch);
			$json = json_decode($data, true);
			if (!isset($json['response'])) {
				throw new \Exception($data);
			}
			usleep(mt_rand(1000000, 2000000));
			return $json['response'];
		}
	}

	//echo "ok";

?>