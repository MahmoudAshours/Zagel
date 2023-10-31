<?php

require 'vendor/autoload.php'; // Include Composer's autoloader
use GuzzleHttp\Client;


$conn = sqlConnection();
sqlSelectDB($conn);
$res = getScheduledJobs($conn, $argv);

if ($res->num_rows != 0) {
	foreach ($res->fetch_all(MYSQLI_ASSOC) as $userData) {

		$isSuccessfulConversion =  convertPDFtoPNG($userData['pdf_path'], $userData["current_page"], $userData["incremental_factor"]);
		if ($isSuccessfulConversion) {
			$receivers = explode(",", $userData["receivers"]);
			$pdfPathName =  basename($userData["pdf_path"]);
			$directoryPath = str_replace('.pdf', '', $pdfPathName);
			$contents = scandir($directoryPath);
			foreach ($contents as $item) {
				if ($item != "." && $item != "..") {
					for ($i = 0; $i < count($receivers); $i++) {
						sendImagesWhatsapp($receivers[$i], $userData["whatsapp_key"], "{$directoryPath}/{$item}");
					}
				}
			}
			$newCurrPage = (int)  $userData["current_page"] + (int) $userData["incremental_factor"];
			updateUserJobs($conn, (int)$userData["id"], $newCurrPage);
			exec("rm -r {$directoryPath} && rm $pdfPathName &");
		}
	}
	$conn->close();
} else {
	echo "No scheduled jobs";
	$conn->close();
}


function sqlConnection(): mysqli
{
	$servername = "localhost";
	$username = "root";
	$password = "";

	$conn = new mysqli($servername, $username, $password);

	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}
	return $conn;
}

function sqlSelectDB(mysqli $conn): void
{
	$conn->select_db("Message_Sender");
}

function convertPDFtoPNG(string $pdfPath, int $start, int $factor): int
{
	$success = false;
	$basePath = '/Users/ashour/Dev/PHP\ projects/book_scheduler/storage/app/';
	exec("php book_mock.php $basePath/$pdfPath $start $factor", $test, $resultcode);
	var_dump($resultcode);
	if ($resultcode == 0) {
		$success = true;
	} else
	if ($resultcode == 255) {
		syslog(LOG_ALERT, "Error in pdf_script of path $pdfPath");
	} else if ($resultcode == 3) {
		// handle if it is more than the end page
	}
	return $success;
}

function getTodaysInterval(): int
{
	$today  = date('l');
	$return_value = match ($today) {
		'Sunday' => 1,
		'Monday' => 2,
		'Tuesday' => 3,
		'Wednesday' => 4,
		'Thursday' => 5,
		'Friday' => 6,
		'Saturday' => 7,
	};
	return $return_value;
}

function sendImagesWhatsapp(string $number, string $key, string $imagePath): void
{
	// URL you want to send the POST request to
	$url = 'http://157.230.26.46:3333/message/image';
	$client = new Client([
		'timeout' => 15
	]);

	$data = [
		'query' => [
			'key' => $key,
		],
		'multipart' => [
			[
				'name' => 'file',
				'contents' => fopen($imagePath, 'r'),
				'filename' => 'img.jpg'
			],
			[
				'name' => 'id',
				'contents' =>  $number,
			], [
				'name' => 'caption',
				'contents' =>  'test',
			]
		],
	];

	$response = $client->requestAsync('POST', $url, $data);

	$body = $response->wait();
}

function getScheduledJobs(mysqli $conn, array $argv): bool|mysqli_result
{
	$interval = getTodaysInterval();
	$sql = "SELECT * FROM subscriber_jobs INNER JOIN subscriber_data ON subscriber_data.id=subscriber_jobs.user_id WHERE job_time = ? AND (`interval`=0 OR `interval`=?);";
	$stmt = $conn->prepare($sql);
	// Bind the parameters
	$stmt->bind_param("si", $argv[1], $interval); // "si" means a string and an integer
	// Execute the query
	$stmt->execute();
	// Fetch results as needed
	$res = $stmt->get_result();
	// Close the statement
	$stmt->close();
	return $res;
}

function updateUserJobs(mysqli $conn, int $id, int $newCurrPage)
{
	$sql = "UPDATE subscriber_jobs SET current_page=? WHERE id = ?";
	$stmt = $conn->prepare($sql);
	// Bind the parameters
	$stmt->bind_param("ii", $newCurrPage, $id);
	// Execute the query
	$stmt->execute();
	// Fetch results as needed
	$res = $stmt->get_result();
	// Close the statement
	$stmt->close();
}
