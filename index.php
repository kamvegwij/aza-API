<?php
	require("config.php");
	require __DIR__."/vendor/autoload.php";
	use GuzzleHttp\Client;

	$BIN_KEY = BIN_KEY;

	$client = new Client([
		// Base URI is used with relative requests
		// 'base_uri' => 'https://localhost/aza-explorers',
		'base_uri' => 'https://json.extendsclass.com/bin/'.$BIN_KEY,
		'headers' => [
			'Security-key' => SECURITY_KEY,
			'Api-key' => API_KEY,
		],
		'timeout'  => 2.0,
	]);

	$res = $client->request('GET', '');

	# Convert our API response data into a dictionary:
	$json = json_decode($res->getBody(), true);
	$json_data  = $json['data'];
	
	# get the command specified from Godot
	$command = $json['command'];
	
	function print_response($dictionary = [], $error = "none"){
		$string = "";
		
		# Convert our dictionary into a JSON string:
		$string = "{\"error\": \"$error\", \"data\": ".json_encode($dictionary) ."}";
		
		# Print out our json to Godot!
		echo $string;
		die;
	}

	# echo "command:".$command."<br>";
	
	# Set connection properties for our database:
	$sql_host = HOST;	# Where our database is located
	$sql_db = DATABASE;			# Name of our database
	$sql_username = USERNAME;		    # Login username for our database
	$sql_password = PASSWORD;	    # Login password for our database
	$port = PORT;
	
	# Set up our data in a format that PDO understands:
	$dsn = "mysql:dbname=$sql_db;host=$sql_host;charset=utf8mb4;port=$port";
	$pdo = null;
	
	try { # Attempt to connect:
		$pdo = new PDO($dsn, $sql_username, $sql_password);
	} 
	catch (\PDOException $e){
		# If something went wrong, return an error to Godot:
		print_response([], "db_login_error");
		die;
	}	
	
	# Execute our Godot commands:
	switch ($command){
		
		# Generate a single-use nonce for our user and return it to Godot:
		case "get_nonce":
			# Generate random bytes that we can hash:
			$bytes = random_bytes(32);
			$nonce = hash('sha256', $bytes);
			
			# Form our SQL template:
			$template = "INSERT INTO `nonces` (ip_address, nonce) VALUES (:ip, :nonce) ON DUPLICATE KEY UPDATE nonce = :nonce_update";
			
			# Prepare and send via PDO:
			$sth = $pdo -> prepare($template);
			$sth -> execute(["ip" => $_SERVER["REMOTE_ADDR"], "nonce" => $nonce, "nonce_update" => $nonce]);
			//echo $_SERVER["REMOTE_ADDR"];
			//echo $nonce;
			
			# Send the nonce back to Godot:
			print_response(["nonce" => $nonce]);	
		die;
	
		/*
		 * Custom  CRUD requests handler
		*/
		case "get_user":
			// check if all fields are set
			if ( !isset($json_data['userID']) ) {
				print_response([], "missing_userID");
				die; // end connection
			} 

			$template = "SELECT * FROM `users` WHERE userID = :userID";

			# Prepare and send via PDO:
			$stmt = $pdo->prepare($template);
			$stmt->execute([":userID" => $json_data['useID']]);

			$data = $stmt->fetch(PDO::FETCH_ASSOC);

			print_response($data);
		die;

		case "check_user":	
			// check if all fields are set
			if (!isset($json_data['username']) || !isset($json_data['password'])) {
				print_response($json_data, "missing_username_or_password");
				die; // end connection
			} 
			
			$username = $json_data['username'];
			$password = $json_data['password'];

			$template = "SELECT * FROM `users` WHERE username = :username";

			# Prepare and send via PDO:
			$stmt = $pdo->prepare($template);
			$stmt->execute(["username" => $username]);

			# Grab all the resulting data from our request:
			$data = $stmt->fetch(PDO::FETCH_ASSOC);

			// if the $result is False, username does not exist
			if ( !$data ) {
				print_response([], "invalid_username");
				die; // end connection
			} 

			$hash_password = $data['password'];
			if (password_verify($password, $hash_password)) {
				print_response($data);
			}
			else print_response([], "invalid_password");
			
			$pdo = null; // close Database connection
		die; // end connection

		case "update_user":
			// update variables, identical to Database table setup
			try {
				$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

				$template = "UPDATE `users` SET name=?, surname=?, grade=?, username=?, password=?,
				 type=?, language=?, lastLoggedIn=?, flag=? WHERE userID =?";
				
				$stmt = $pdo->prepare($template);
				// print_response([], "b4 execute");
				$stmt->execute(array(
					$json_data['name'],
					$json_data['surname'],
					$json_data['grade'],
					$json_data['username'],
					$json_data['password'],
					$json_data['type'],
					$json_data['language'],
					$json_data['lastLoggedIn'],
					$json_data['flag'],
					$json_data['userID']
				));

				$data = $stmt->fetch(PDO::FETCH_ASSOC);

				print_response([]);
			} catch (PDOException $ex) {
				print_response([], $ex->getMessage());
				die;
			}
		die; // end connection

		case "add_level_one_data":
			// TODO		
		die;

		case "update_level_one_data":
			// TODO
		die;

		# Fetch a number of scores from our table:
		case "get_scores":
			# Determine which range of scores we want:
			$score_offset = 0; 
			$score_number = 10;
			
			# Check if Godot set some preferences and adjust accordingly:
			if (isset($json_data['score_offset']))
				$score_start = max(0, (int) $json_data['score_offset']);
				
			if (isset($json_data['score_number']))
				$score_number = max(1, (int) $json_data['score_number']);
				
			# Form our SQL request template:
			$template = "SELECT * FROM `highscores` ORDER BY score DESC LIMIT :number OFFSET :offset";
			
			# Prepare and send the actual request to the database:
			$sth = $pdo -> prepare($template);
			$sth -> bindValue("number", $score_number, PDO::PARAM_INT);
			$sth -> bindValue("offset", $score_offset, PDO::PARAM_INT);
			$sth -> execute();
			
			# Grab all the resulting data from our request:
			$data = $sth -> fetchAll(PDO::FETCH_ASSOC);
			
			# Add the size of our result to the Godot structure:
			$data["size"] = sizeof($data);
			
			print_response($data);
		die;

		# Add a score to our table:
		case "add_score":
			# Check that we were given a score and username:
			if (!isset($json_data['score'])){
				print_response([], "missing_score");
				die;
			}
			
			if (!isset($json_data['username'])){
				print_response([], "missing_username");
				die;
			}
			
			# Make sure our username is under 24 characters:
			$username = $json_data['username'];
			if (strlen($username) > 24)
				$username = substr($username, 24);
			
			
			# Form our SQL request string:
			$template = "INSERT INTO `highscores` (username, score) VALUES (:username, :score) ON DUPLICATE KEY UPDATE score = GREATEST(score, VALUES(score))";
			
			# Prepare and send the request to the DB:
			$sth = $pdo -> prepare($template);
			$sth -> execute(["username" => $username, "score" => $json_data['score']]);
			
			# Print back an empty response saying there was no issue:
			print_response();
		die;
	
		# Handle invalid requests:
		default:
			print_response([], "invalid_command");
			die;	
	}
	
?>
