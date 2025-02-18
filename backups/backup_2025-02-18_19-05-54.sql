-- CCS System Backup - Generated on 2025-02-18 19:05:54



CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=310 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO activity_logs VALUES(''1'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-12 16:39:51'');
INSERT INTO activity_logs VALUES(''2'',''2'',''login'',''User logged in as super_admin'',NULL,''2025-02-12 16:39:53'');
INSERT INTO activity_logs VALUES(''3'',''2'',''logout'',''User logged out'',''::1'',''2025-02-12 16:53:12'');
INSERT INTO activity_logs VALUES(''4'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-12 16:53:17'');
INSERT INTO activity_logs VALUES(''5'',''2'',''login'',''User logged in as super_admin'',NULL,''2025-02-12 16:53:17'');
INSERT INTO activity_logs VALUES(''6'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-12 18:13:25'');
INSERT INTO activity_logs VALUES(''7'',''2'',''login'',''User logged in as super_admin'',NULL,''2025-02-12 18:13:26'');
INSERT INTO activity_logs VALUES(''8'',''2'',''logout'',''User logged out'',''::1'',''2025-02-12 18:15:00'');
INSERT INTO activity_logs VALUES(''9'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-12 18:15:04'');
INSERT INTO activity_logs VALUES(''10'',''2'',''login'',''User logged in as super_admin'',NULL,''2025-02-12 18:15:05'');
INSERT INTO activity_logs VALUES(''11'',''2'',''logout'',''User logged out'',''::1'',''2025-02-12 18:18:31'');
INSERT INTO activity_logs VALUES(''12'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-12 18:18:34'');
INSERT INTO activity_logs VALUES(''13'',''2'',''login'',''User logged in as super_admin'',NULL,''2025-02-12 18:18:34'');
INSERT INTO activity_logs VALUES(''14'',''2'',''logout'',''User logged out'',''::1'',''2025-02-12 18:18:34'');
INSERT INTO activity_logs VALUES(''15'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-12 18:18:41'');
INSERT INTO activity_logs VALUES(''16'',''2'',''login'',''User logged in as super_admin'',NULL,''2025-02-12 18:18:41'');
INSERT INTO activity_logs VALUES(''17'',''2'',''logout'',''User logged out'',''::1'',''2025-02-12 18:18:41'');
INSERT INTO activity_logs VALUES(''18'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-12 18:19:00'');
INSERT INTO activity_logs VALUES(''19'',''2'',''login'',''User logged in as super_admin'',NULL,''2025-02-12 18:19:00'');
INSERT INTO activity_logs VALUES(''20'',''2'',''logout'',''User logged out'',''::1'',''2025-02-12 18:19:00'');
INSERT INTO activity_logs VALUES(''21'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-12 18:21:09'');
INSERT INTO activity_logs VALUES(''22'',''2'',''login'',''User logged in as super_admin'',NULL,''2025-02-12 18:21:09'');
INSERT INTO activity_logs VALUES(''23'',''2'',''logout'',''User logged out'',''::1'',''2025-02-12 18:21:09'');
INSERT INTO activity_logs VALUES(''24'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-12 18:24:06'');
INSERT INTO activity_logs VALUES(''25'',''2'',''login'',''User logged in as super_admin'',NULL,''2025-02-12 18:24:06'');
INSERT INTO activity_logs VALUES(''26'',''2'',''logout'',''User logged out'',''::1'',''2025-02-12 18:24:07'');
INSERT INTO activity_logs VALUES(''27'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-12 18:32:30'');
INSERT INTO activity_logs VALUES(''28'',''2'',''login'',''User logged in as super_admin'',NULL,''2025-02-12 18:32:30'');
INSERT INTO activity_logs VALUES(''29'',''2'',''logout'',''User logged out'',''::1'',''2025-02-12 18:32:30'');
INSERT INTO activity_logs VALUES(''30'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-12 19:04:40'');
INSERT INTO activity_logs VALUES(''31'',''2'',''login'',''User logged in as super_admin'',NULL,''2025-02-12 19:04:40'');
INSERT INTO activity_logs VALUES(''32'',''2'',''logout'',''User logged out'',''::1'',''2025-02-12 19:04:40'');
INSERT INTO activity_logs VALUES(''33'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-12 19:08:08'');
INSERT INTO activity_logs VALUES(''34'',''2'',''login'',''User logged in as super_admin'',NULL,''2025-02-12 19:08:08'');
INSERT INTO activity_logs VALUES(''35'',''2'',''logout'',''User logged out'',''::1'',''2025-02-12 19:08:08'');
INSERT INTO activity_logs VALUES(''36'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-12 19:10:17'');
INSERT INTO activity_logs VALUES(''37'',''2'',''login'',''User logged in as super_admin'',NULL,''2025-02-12 19:10:17'');
INSERT INTO activity_logs VALUES(''38'',''2'',''logout'',''User logged out'',''::1'',''2025-02-12 19:10:17'');
INSERT INTO activity_logs VALUES(''39'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-12 19:10:25'');
INSERT INTO activity_logs VALUES(''40'',''2'',''logout'',''User logged out'',''::1'',''2025-02-12 19:10:25'');
INSERT INTO activity_logs VALUES(''41'',''2'',''login'',''User logged in as admin'',NULL,''2025-02-12 19:10:25'');
INSERT INTO activity_logs VALUES(''42'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-13 16:37:59'');
INSERT INTO activity_logs VALUES(''43'',''2'',''login'',''User logged in as super_admin'',NULL,''2025-02-13 16:38:00'');
INSERT INTO activity_logs VALUES(''44'',''2'',''logout'',''User logged out'',''::1'',''2025-02-13 16:38:00'');
INSERT INTO activity_logs VALUES(''45'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-13 19:59:44'');
INSERT INTO activity_logs VALUES(''46'',''2'',''logout'',''User logged out'',''::1'',''2025-02-13 19:59:44'');
INSERT INTO activity_logs VALUES(''47'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-13 20:09:40'');
INSERT INTO activity_logs VALUES(''48'',''2'',''logout'',''User logged out'',''::1'',''2025-02-13 20:09:41'');
INSERT INTO activity_logs VALUES(''49'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-13 20:10:42'');
INSERT INTO activity_logs VALUES(''50'',''2'',''logout'',''User logged out'',''::1'',''2025-02-13 20:10:42'');
INSERT INTO activity_logs VALUES(''51'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-13 20:16:49'');
INSERT INTO activity_logs VALUES(''52'',''2'',''logout'',''User logged out'',''::1'',''2025-02-13 20:16:50'');
INSERT INTO activity_logs VALUES(''53'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-13 20:21:31'');
INSERT INTO activity_logs VALUES(''54'',''2'',''logout'',''User logged out'',''::1'',''2025-02-13 20:21:31'');
INSERT INTO activity_logs VALUES(''55'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-13 20:33:41'');
INSERT INTO activity_logs VALUES(''56'',''2'',''logout'',''User logged out'',''::1'',''2025-02-13 20:33:41'');
INSERT INTO activity_logs VALUES(''57'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-13 20:43:35'');
INSERT INTO activity_logs VALUES(''58'',''2'',''logout'',''User logged out'',''::1'',''2025-02-13 20:43:35'');
INSERT INTO activity_logs VALUES(''59'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-13 20:55:48'');
INSERT INTO activity_logs VALUES(''60'',''2'',''logout'',''User logged out'',''::1'',''2025-02-13 20:55:48'');
INSERT INTO activity_logs VALUES(''61'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-13 21:00:49'');
INSERT INTO activity_logs VALUES(''62'',''2'',''logout'',''User logged out'',''::1'',''2025-02-13 21:10:58'');
INSERT INTO activity_logs VALUES(''63'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-13 21:11:07'');
INSERT INTO activity_logs VALUES(''64'',''2'',''logout'',''User logged out'',''::1'',''2025-02-13 21:11:07'');
INSERT INTO activity_logs VALUES(''65'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-13 21:11:43'');
INSERT INTO activity_logs VALUES(''66'',''2'',''logout'',''User logged out'',''::1'',''2025-02-13 21:16:20'');
INSERT INTO activity_logs VALUES(''67'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-13 21:16:28'');
INSERT INTO activity_logs VALUES(''68'',''2'',''logout'',''User logged out'',''::1'',''2025-02-13 21:16:28'');
INSERT INTO activity_logs VALUES(''69'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-13 21:18:02'');
INSERT INTO activity_logs VALUES(''70'',''2'',''logout'',''User logged out'',''::1'',''2025-02-13 21:18:39'');
INSERT INTO activity_logs VALUES(''71'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-13 21:18:51'');
INSERT INTO activity_logs VALUES(''72'',''2'',''logout'',''User logged out'',''::1'',''2025-02-13 21:18:52'');
INSERT INTO activity_logs VALUES(''73'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-13 21:23:01'');
INSERT INTO activity_logs VALUES(''74'',''2'',''logout'',''User logged out'',''::1'',''2025-02-13 21:27:39'');
INSERT INTO activity_logs VALUES(''75'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-13 21:27:45'');
INSERT INTO activity_logs VALUES(''76'',''2'',''logout'',''User logged out'',''::1'',''2025-02-13 21:27:45'');
INSERT INTO activity_logs VALUES(''77'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-13 21:28:47'');
INSERT INTO activity_logs VALUES(''78'',''2'',''logout'',''User logged out'',''::1'',''2025-02-13 21:28:47'');
INSERT INTO activity_logs VALUES(''79'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-13 21:31:43'');
INSERT INTO activity_logs VALUES(''80'',''2'',''logout'',''User logged out'',''::1'',''2025-02-13 21:31:43'');
INSERT INTO activity_logs VALUES(''81'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-13 21:34:50'');
INSERT INTO activity_logs VALUES(''82'',''2'',''logout'',''User logged out'',''::1'',''2025-02-13 21:34:50'');
INSERT INTO activity_logs VALUES(''83'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-13 21:34:57'');
INSERT INTO activity_logs VALUES(''84'',''2'',''logout'',''User logged out'',''::1'',''2025-02-13 21:34:57'');
INSERT INTO activity_logs VALUES(''85'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-13 21:37:47'');
INSERT INTO activity_logs VALUES(''86'',''2'',''logout'',''User logged out'',''::1'',''2025-02-13 21:37:47'');
INSERT INTO activity_logs VALUES(''87'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-13 21:38:51'');
INSERT INTO activity_logs VALUES(''88'',''2'',''logout'',''User logged out'',''::1'',''2025-02-13 21:38:51'');
INSERT INTO activity_logs VALUES(''89'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-13 21:41:00'');
INSERT INTO activity_logs VALUES(''90'',''2'',''logout'',''User logged out'',''::1'',''2025-02-13 21:44:57'');
INSERT INTO activity_logs VALUES(''91'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-13 21:45:30'');
INSERT INTO activity_logs VALUES(''92'',''2'',''logout'',''User logged out'',''::1'',''2025-02-13 21:45:50'');
INSERT INTO activity_logs VALUES(''93'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-13 21:47:09'');
INSERT INTO activity_logs VALUES(''94'',''2'',''logout'',''User logged out'',''::1'',''2025-02-13 21:49:41'');
INSERT INTO activity_logs VALUES(''95'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-13 21:49:49'');
INSERT INTO activity_logs VALUES(''96'',''2'',''logout'',''User logged out'',''::1'',''2025-02-13 21:49:55'');
INSERT INTO activity_logs VALUES(''97'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-13 21:53:29'');
INSERT INTO activity_logs VALUES(''98'',''2'',''logout'',''User logged out'',''::1'',''2025-02-13 21:54:07'');
INSERT INTO activity_logs VALUES(''99'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-13 21:54:15'');
INSERT INTO activity_logs VALUES(''100'',''2'',''logout'',''User logged out'',''::1'',''2025-02-13 21:54:21'');
INSERT INTO activity_logs VALUES(''101'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-13 21:55:03'');
INSERT INTO activity_logs VALUES(''102'',''2'',''logout'',''User logged out'',''::1'',''2025-02-13 21:55:10'');
INSERT INTO activity_logs VALUES(''103'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-13 21:55:46'');
INSERT INTO activity_logs VALUES(''104'',''2'',''logout'',''User logged out'',''::1'',''2025-02-13 21:55:51'');
INSERT INTO activity_logs VALUES(''105'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-13 21:56:59'');
INSERT INTO activity_logs VALUES(''106'',''2'',''logout'',''User logged out'',''::1'',''2025-02-13 22:00:03'');
INSERT INTO activity_logs VALUES(''107'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-13 22:00:09'');
INSERT INTO activity_logs VALUES(''108'',''2'',''logout'',''User logged out'',''::1'',''2025-02-13 22:02:17'');
INSERT INTO activity_logs VALUES(''109'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-13 22:02:24'');
INSERT INTO activity_logs VALUES(''110'',''2'',''logout'',''User logged out'',''::1'',''2025-02-13 22:02:27'');
INSERT INTO activity_logs VALUES(''111'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-14 09:38:29'');
INSERT INTO activity_logs VALUES(''112'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-14 13:20:04'');
INSERT INTO activity_logs VALUES(''113'',''2'',''logout'',''User logged out'',''::1'',''2025-02-14 13:59:56'');
INSERT INTO activity_logs VALUES(''114'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-14 14:00:05'');
INSERT INTO activity_logs VALUES(''115'',''2'',''logout'',''User logged out'',''::1'',''2025-02-14 14:03:06'');
INSERT INTO activity_logs VALUES(''116'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-14 14:03:15'');
INSERT INTO activity_logs VALUES(''117'',''2'',''admin_created'',''Created new admin account for ADMIN2 test (test_admin@gmail.com)'',''::1'',''2025-02-14 14:05:41'');
INSERT INTO activity_logs VALUES(''118'',''2'',''admin_created'',''Created new admin account for ADMIN2 test (test_admin@gmail.com)'',''::1'',''2025-02-14 14:06:03'');
INSERT INTO activity_logs VALUES(''119'',''2'',''logout'',''User logged out'',''::1'',''2025-02-14 14:09:35'');
INSERT INTO activity_logs VALUES(''120'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-14 14:09:41'');
INSERT INTO activity_logs VALUES(''121'',''5'',''logout'',''User logged out'',''::1'',''2025-02-14 14:11:03'');
INSERT INTO activity_logs VALUES(''122'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-14 14:11:08'');
INSERT INTO activity_logs VALUES(''123'',''2'',''exam_created'',''Created new exam: Entrance Exam (Part 1)'',''::1'',''2025-02-14 14:42:04'');
INSERT INTO activity_logs VALUES(''124'',''2'',''exam_created'',''Created new exam: Entrance Exam (Part 1)'',''::1'',''2025-02-14 14:42:13'');
INSERT INTO activity_logs VALUES(''125'',''2'',''question_added'',''Added new question to exam ID: 2'',''::1'',''2025-02-14 14:44:11'');
INSERT INTO activity_logs VALUES(''126'',''2'',''question_added'',''Added new question to exam ID: 2'',''::1'',''2025-02-14 14:44:42'');
INSERT INTO activity_logs VALUES(''127'',''2'',''question_added'',''Added new question to exam ID: 2'',''::1'',''2025-02-14 14:44:57'');
INSERT INTO activity_logs VALUES(''128'',''2'',''question_added'',''Added new question to exam ID: 2'',''::1'',''2025-02-14 14:45:11'');
INSERT INTO activity_logs VALUES(''129'',''2'',''question_added'',''Added new question to exam ID: 2'',''::1'',''2025-02-14 14:45:41'');
INSERT INTO activity_logs VALUES(''130'',''2'',''exam_updated'',''Updated exam ID: 1'',''::1'',''2025-02-14 14:53:54'');
INSERT INTO activity_logs VALUES(''131'',''2'',''exam_updated'',''Updated exam ID: 1'',''::1'',''2025-02-14 14:54:06'');
INSERT INTO activity_logs VALUES(''135'',''9'',''register'',''New user registration'',''::1'',''2025-02-14 18:29:37'');
INSERT INTO activity_logs VALUES(''136'',''10'',''register'',''New user registration'',''::1'',''2025-02-14 18:32:05'');
INSERT INTO activity_logs VALUES(''137'',''11'',''register'',''New user registration'',''::1'',''2025-02-14 18:34:16'');
INSERT INTO activity_logs VALUES(''138'',''12'',''register'',''New user registration'',''::1'',''2025-02-14 18:38:37'');
INSERT INTO activity_logs VALUES(''139'',''13'',''register'',''New user registration'',''::1'',''2025-02-14 18:43:12'');
INSERT INTO activity_logs VALUES(''140'',''14'',''register'',''New user registration'',''::1'',''2025-02-14 18:49:44'');
INSERT INTO activity_logs VALUES(''141'',''15'',''register'',''New user registration'',''::1'',''2025-02-14 18:55:38'');
INSERT INTO activity_logs VALUES(''142'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-14 18:56:11'');
INSERT INTO activity_logs VALUES(''143'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-14 19:53:52'');
INSERT INTO activity_logs VALUES(''144'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-14 20:51:43'');
INSERT INTO activity_logs VALUES(''145'',''2'',''logout'',''User logged out'',''::1'',''2025-02-14 22:02:21'');
INSERT INTO activity_logs VALUES(''146'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-14 22:02:29'');
INSERT INTO activity_logs VALUES(''147'',''5'',''logout'',''User logged out'',''::1'',''2025-02-14 22:10:59'');
INSERT INTO activity_logs VALUES(''148'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-14 22:11:05'');
INSERT INTO activity_logs VALUES(''149'',''2'',''logout'',''User logged out'',''::1'',''2025-02-14 22:24:49'');
INSERT INTO activity_logs VALUES(''150'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-14 22:25:05'');
INSERT INTO activity_logs VALUES(''151'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 10:23:41'');
INSERT INTO activity_logs VALUES(''152'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 10:24:25'');
INSERT INTO activity_logs VALUES(''153'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 10:24:29'');
INSERT INTO activity_logs VALUES(''154'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 10:24:32'');
INSERT INTO activity_logs VALUES(''155'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 10:24:55'');
INSERT INTO activity_logs VALUES(''156'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 10:25:11'');
INSERT INTO activity_logs VALUES(''157'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 10:25:14'');
INSERT INTO activity_logs VALUES(''158'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 10:39:45'');
INSERT INTO activity_logs VALUES(''159'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 10:39:48'');
INSERT INTO activity_logs VALUES(''160'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 12:01:21'');
INSERT INTO activity_logs VALUES(''161'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 12:01:23'');
INSERT INTO activity_logs VALUES(''162'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 12:01:28'');
INSERT INTO activity_logs VALUES(''163'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 12:01:30'');
INSERT INTO activity_logs VALUES(''164'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 12:01:35'');
INSERT INTO activity_logs VALUES(''165'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 12:03:15'');
INSERT INTO activity_logs VALUES(''166'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 12:03:46'');
INSERT INTO activity_logs VALUES(''167'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 12:03:50'');
INSERT INTO activity_logs VALUES(''168'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 12:07:21'');
INSERT INTO activity_logs VALUES(''169'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 12:07:22'');
INSERT INTO activity_logs VALUES(''170'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 12:09:25'');
INSERT INTO activity_logs VALUES(''171'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 12:09:27'');
INSERT INTO activity_logs VALUES(''172'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 12:12:26'');
INSERT INTO activity_logs VALUES(''173'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 12:12:29'');
INSERT INTO activity_logs VALUES(''174'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 12:13:56'');
INSERT INTO activity_logs VALUES(''175'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 12:13:59'');
INSERT INTO activity_logs VALUES(''176'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 12:15:43'');
INSERT INTO activity_logs VALUES(''177'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 12:15:45'');
INSERT INTO activity_logs VALUES(''178'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 12:15:58'');
INSERT INTO activity_logs VALUES(''179'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 12:16:00'');
INSERT INTO activity_logs VALUES(''180'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 12:18:24'');
INSERT INTO activity_logs VALUES(''181'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 12:26:01'');
INSERT INTO activity_logs VALUES(''182'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 12:30:01'');
INSERT INTO activity_logs VALUES(''183'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 12:30:07'');
INSERT INTO activity_logs VALUES(''184'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 12:33:27'');
INSERT INTO activity_logs VALUES(''185'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 12:33:30'');
INSERT INTO activity_logs VALUES(''186'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 12:37:51'');
INSERT INTO activity_logs VALUES(''187'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 12:37:54'');
INSERT INTO activity_logs VALUES(''188'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 13:11:38'');
INSERT INTO activity_logs VALUES(''189'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 13:21:52'');
INSERT INTO activity_logs VALUES(''190'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 13:21:57'');
INSERT INTO activity_logs VALUES(''191'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 13:25:56'');
INSERT INTO activity_logs VALUES(''192'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 13:26:08'');
INSERT INTO activity_logs VALUES(''193'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 13:27:51'');
INSERT INTO activity_logs VALUES(''194'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 13:27:54'');
INSERT INTO activity_logs VALUES(''195'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 13:31:49'');
INSERT INTO activity_logs VALUES(''196'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 13:31:51'');
INSERT INTO activity_logs VALUES(''197'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 13:36:27'');
INSERT INTO activity_logs VALUES(''198'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 13:36:29'');
INSERT INTO activity_logs VALUES(''199'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 13:40:44'');
INSERT INTO activity_logs VALUES(''200'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 13:40:45'');
INSERT INTO activity_logs VALUES(''201'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 13:41:34'');
INSERT INTO activity_logs VALUES(''202'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 13:42:18'');
INSERT INTO activity_logs VALUES(''203'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 13:52:08'');
INSERT INTO activity_logs VALUES(''204'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 13:52:09'');
INSERT INTO activity_logs VALUES(''205'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 13:52:11'');
INSERT INTO activity_logs VALUES(''206'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 13:52:11'');
INSERT INTO activity_logs VALUES(''207'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-15 13:52:17'');
INSERT INTO activity_logs VALUES(''208'',''2'',''logout'',''User logged out'',''::1'',''2025-02-15 13:52:21'');
INSERT INTO activity_logs VALUES(''209'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 13:52:28'');
INSERT INTO activity_logs VALUES(''210'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 13:55:46'');
INSERT INTO activity_logs VALUES(''211'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 13:55:48'');
INSERT INTO activity_logs VALUES(''212'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 13:56:34'');
INSERT INTO activity_logs VALUES(''213'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 13:56:35'');
INSERT INTO activity_logs VALUES(''214'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 13:56:54'');
INSERT INTO activity_logs VALUES(''215'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 14:16:21'');
INSERT INTO activity_logs VALUES(''216'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 15:15:45'');
INSERT INTO activity_logs VALUES(''217'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 15:15:48'');
INSERT INTO activity_logs VALUES(''218'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 16:24:18'');
INSERT INTO activity_logs VALUES(''219'',''16'',''register'',''New user registration'',''::1'',''2025-02-15 16:33:10'');
INSERT INTO activity_logs VALUES(''220'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 16:36:21'');
INSERT INTO activity_logs VALUES(''221'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 17:16:33'');
INSERT INTO activity_logs VALUES(''222'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 17:16:35'');
INSERT INTO activity_logs VALUES(''223'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 17:42:05'');
INSERT INTO activity_logs VALUES(''224'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 17:42:58'');
INSERT INTO activity_logs VALUES(''225'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 17:43:13'');
INSERT INTO activity_logs VALUES(''226'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 17:43:15'');
INSERT INTO activity_logs VALUES(''227'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 17:50:07'');
INSERT INTO activity_logs VALUES(''228'',''15'',''login'',''User logged in successfully'',''::1'',''2025-02-15 17:59:34'');
INSERT INTO activity_logs VALUES(''229'',''15'',''logout'',''User logged out'',''::1'',''2025-02-15 17:59:56'');
INSERT INTO activity_logs VALUES(''230'',''15'',''login'',''User logged in successfully'',''::1'',''2025-02-15 18:00:20'');
INSERT INTO activity_logs VALUES(''231'',''15'',''logout'',''User logged out'',''::1'',''2025-02-15 18:00:22'');
INSERT INTO activity_logs VALUES(''232'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 18:00:48'');
INSERT INTO activity_logs VALUES(''233'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 18:01:40'');
INSERT INTO activity_logs VALUES(''234'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 18:01:42'');
INSERT INTO activity_logs VALUES(''235'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 18:02:05'');
INSERT INTO activity_logs VALUES(''236'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 18:02:07'');
INSERT INTO activity_logs VALUES(''237'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 18:02:40'');
INSERT INTO activity_logs VALUES(''238'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 18:13:20'');
INSERT INTO activity_logs VALUES(''239'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 18:14:07'');
INSERT INTO activity_logs VALUES(''240'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 18:14:10'');
INSERT INTO activity_logs VALUES(''241'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 18:14:16'');
INSERT INTO activity_logs VALUES(''242'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 18:21:56'');
INSERT INTO activity_logs VALUES(''243'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 18:22:00'');
INSERT INTO activity_logs VALUES(''244'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 18:22:03'');
INSERT INTO activity_logs VALUES(''245'',''15'',''login'',''User logged in successfully'',''::1'',''2025-02-15 18:22:08'');
INSERT INTO activity_logs VALUES(''246'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 18:34:57'');
INSERT INTO activity_logs VALUES(''247'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 18:35:09'');
INSERT INTO activity_logs VALUES(''248'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-15 18:35:34'');
INSERT INTO activity_logs VALUES(''249'',''2'',''exam_publish'',''Exam (ID: 1) status changed to published'',''::1'',''2025-02-15 18:36:18'');
INSERT INTO activity_logs VALUES(''250'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-15 19:26:47'');
INSERT INTO activity_logs VALUES(''251'',''5'',''logout'',''User logged out'',''::1'',''2025-02-15 19:26:53'');
INSERT INTO activity_logs VALUES(''252'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-15 19:27:00'');
INSERT INTO activity_logs VALUES(''253'',''2'',''question_added'',''Added new question to exam ID: 1'',''::1'',''2025-02-15 19:28:07'');
INSERT INTO activity_logs VALUES(''254'',''2'',''question_added'',''Added new question to exam ID: 1'',''::1'',''2025-02-15 19:28:18'');
INSERT INTO activity_logs VALUES(''255'',''2'',''logout'',''User logged out'',''::1'',''2025-02-15 19:28:22'');
INSERT INTO activity_logs VALUES(''256'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-15 19:38:05'');
INSERT INTO activity_logs VALUES(''257'',''2'',''exam_publish'',''Exam (ID: 2) status changed to published'',''::1'',''2025-02-15 19:38:24'');
INSERT INTO activity_logs VALUES(''258'',''15'',''login'',''User logged in successfully'',''::1'',''2025-02-15 21:59:17'');
INSERT INTO activity_logs VALUES(''259'',''15'',''login'',''User logged in successfully'',''::1'',''2025-02-16 18:57:43'');
INSERT INTO activity_logs VALUES(''260'',''15'',''logout'',''User logged out'',''::1'',''2025-02-16 20:32:34'');
INSERT INTO activity_logs VALUES(''261'',''15'',''login'',''User logged in successfully'',''::1'',''2025-02-16 20:32:39'');
INSERT INTO activity_logs VALUES(''262'',''15'',''logout'',''User logged out'',''::1'',''2025-02-16 21:03:45'');
INSERT INTO activity_logs VALUES(''263'',''14'',''login'',''User logged in successfully'',''::1'',''2025-02-16 21:03:51'');
INSERT INTO activity_logs VALUES(''264'',''14'',''logout'',''User logged out'',''::1'',''2025-02-16 22:09:38'');
INSERT INTO activity_logs VALUES(''265'',''5'',''login'',''User logged in successfully'',''::1'',''2025-02-16 22:09:53'');
INSERT INTO activity_logs VALUES(''266'',''5'',''logout'',''User logged out'',''::1'',''2025-02-16 22:11:16'');
INSERT INTO activity_logs VALUES(''267'',''13'',''login'',''User logged in successfully'',''::1'',''2025-02-16 22:11:25'');
INSERT INTO activity_logs VALUES(''268'',''13'',''logout'',''User logged out'',''::1'',''2025-02-16 22:28:49'');
INSERT INTO activity_logs VALUES(''269'',''12'',''login'',''User logged in successfully'',''::1'',''2025-02-16 22:28:59'');
INSERT INTO activity_logs VALUES(''270'',''12'',''logout'',''User logged out'',''::1'',''2025-02-16 22:29:15'');
INSERT INTO activity_logs VALUES(''271'',''12'',''login'',''User logged in successfully'',''::1'',''2025-02-16 22:29:26'');
INSERT INTO activity_logs VALUES(''272'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-17 17:10:14'');
INSERT INTO activity_logs VALUES(''273'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-17 17:16:30'');
INSERT INTO activity_logs VALUES(''274'',''2'',''logout'',''User logged out'',''::1'',''2025-02-17 17:23:29'');
INSERT INTO activity_logs VALUES(''275'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-17 17:23:45'');
INSERT INTO activity_logs VALUES(''276'',''2'',''logout'',''User logged out'',''::1'',''2025-02-17 17:25:08'');
INSERT INTO activity_logs VALUES(''277'',''17'',''register'',''New user registration'',''::1'',''2025-02-17 17:43:41'');
INSERT INTO activity_logs VALUES(''278'',''2'',''admin_created'',''Created new admin account for Jin Sung Ha (hajinsung@gmail.com)'',''::1'',''2025-02-17 17:48:53'');
INSERT INTO activity_logs VALUES(''279'',''18'',''login'',''User logged in successfully'',''::1'',''2025-02-17 17:49:04'');
INSERT INTO activity_logs VALUES(''280'',''18'',''logout'',''User logged out'',''::1'',''2025-02-17 17:49:45'');
INSERT INTO activity_logs VALUES(''281'',''18'',''login'',''User logged in successfully'',''::1'',''2025-02-17 17:52:27'');
INSERT INTO activity_logs VALUES(''282'',''18'',''logout'',''User logged out'',''::1'',''2025-02-17 17:53:07'');
INSERT INTO activity_logs VALUES(''283'',''17'',''login'',''User logged in successfully'',''::1'',''2025-02-17 17:53:13'');
INSERT INTO activity_logs VALUES(''284'',''2'',''exam_draft'',''Exam (ID: 2) status changed to draft'',''::1'',''2025-02-17 18:33:18'');
INSERT INTO activity_logs VALUES(''285'',''2'',''exam_publish'',''Exam (ID: 2) status changed to published'',''::1'',''2025-02-17 18:33:22'');
INSERT INTO activity_logs VALUES(''286'',''17'',''logout'',''User logged out'',''::1'',''2025-02-17 18:36:17'');
INSERT INTO activity_logs VALUES(''287'',''18'',''login'',''User logged in successfully'',''::1'',''2025-02-17 18:39:26'');
INSERT INTO activity_logs VALUES(''288'',''18'',''logout'',''User logged out'',''::1'',''2025-02-17 18:49:09'');
INSERT INTO activity_logs VALUES(''289'',''19'',''register'',''New user registration'',''::1'',''2025-02-17 18:50:29'');
INSERT INTO activity_logs VALUES(''290'',''18'',''login'',''User logged in successfully'',''::1'',''2025-02-17 18:52:02'');
INSERT INTO activity_logs VALUES(''291'',''18'',''logout'',''User logged out'',''::1'',''2025-02-17 18:52:24'');
INSERT INTO activity_logs VALUES(''292'',''19'',''login'',''User logged in successfully'',''::1'',''2025-02-17 18:52:43'');
INSERT INTO activity_logs VALUES(''293'',''17'',''login'',''User logged in successfully'',''::1'',''2025-02-18 17:47:17'');
INSERT INTO activity_logs VALUES(''294'',''17'',''logout'',''User logged out'',''::1'',''2025-02-18 18:35:25'');
INSERT INTO activity_logs VALUES(''295'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-18 18:37:53'');
INSERT INTO activity_logs VALUES(''296'',''2'',''logout'',''User logged out'',''::1'',''2025-02-18 18:45:34'');
INSERT INTO activity_logs VALUES(''297'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-18 18:45:37'');
INSERT INTO activity_logs VALUES(''298'',''2'',''logout'',''User logged out'',''::1'',''2025-02-18 18:45:38'');
INSERT INTO activity_logs VALUES(''299'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-18 18:46:33'');
INSERT INTO activity_logs VALUES(''300'',''2'',''logout'',''User logged out'',''::1'',''2025-02-18 18:46:35'');
INSERT INTO activity_logs VALUES(''301'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-18 18:48:57'');
INSERT INTO activity_logs VALUES(''302'',''2'',''logout'',''User logged out'',''::1'',''2025-02-18 18:48:58'');
INSERT INTO activity_logs VALUES(''303'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-18 18:50:25'');
INSERT INTO activity_logs VALUES(''304'',''2'',''logout'',''User logged out'',''::1'',''2025-02-18 18:50:27'');
INSERT INTO activity_logs VALUES(''305'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-18 18:50:47'');
INSERT INTO activity_logs VALUES(''306'',''2'',''logout'',''User logged out'',''::1'',''2025-02-18 18:51:01'');
INSERT INTO activity_logs VALUES(''307'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-18 18:51:04'');
INSERT INTO activity_logs VALUES(''308'',''2'',''logout'',''User logged out'',''::1'',''2025-02-18 18:51:11'');
INSERT INTO activity_logs VALUES(''309'',''2'',''login'',''User logged in successfully'',''::1'',''2025-02-18 18:51:14'');


CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `admins_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO admins VALUES(''2'',''5'',''ADMIN2'',''test'',''CCS'');
INSERT INTO admins VALUES(''3'',''18'',''Jin Sung'',''Ha'',''CCS'');


CREATE TABLE `announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `target_role` enum('all','admin','applicant') NOT NULL DEFAULT 'all',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `applicant_answers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `applicant_id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `answer` text DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `applicant_id` (`applicant_id`),
  KEY `exam_id` (`exam_id`),
  KEY `question_id` (`question_id`),
  CONSTRAINT `applicant_answers_ibfk_2` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`),
  CONSTRAINT `applicant_answers_ibfk_3` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`),
  CONSTRAINT `fk_applicant_answers_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO applicant_answers VALUES(''2'',''15'',''2'',''1'',''12'',''0'',''0'',''2025-02-16 20:54:38'');
INSERT INTO applicant_answers VALUES(''3'',''15'',''2'',''2'',''qw'',''0'',''0'',''2025-02-16 20:54:38'');
INSERT INTO applicant_answers VALUES(''4'',''15'',''2'',''3'',''456'',''0'',''0'',''2025-02-16 20:54:38'');
INSERT INTO applicant_answers VALUES(''5'',''15'',''2'',''4'',''12'',''0'',''0'',''2025-02-16 20:54:38'');
INSERT INTO applicant_answers VALUES(''6'',''15'',''2'',''5'',''123'',''0'',''0'',''2025-02-16 20:54:38'');


CREATE TABLE `applicants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `applicant_number` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `birth_date` date NOT NULL,
  `school` varchar(255) NOT NULL,
  `course` enum('BSCS','BSIT') NOT NULL,
  `year_level` enum('1st Year','2nd Year','3rd Year','4th Year','5th Year','Graduate') NOT NULL,
  `progress_status` enum('registered','part1_pending','part1_completed','part2_pending','part2_completed','interview_pending','interview_completed','passed','failed') NOT NULL DEFAULT 'registered',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `application_status_id` int(11) DEFAULT NULL,
  `exam_status_id` int(11) DEFAULT NULL,
  `preferred_course` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `application_status_id` (`application_status_id`),
  KEY `exam_status_id` (`exam_status_id`),
  CONSTRAINT `applicants_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `applicants_ibfk_2` FOREIGN KEY (`application_status_id`) REFERENCES `application_status` (`id`),
  CONSTRAINT `applicants_ibfk_3` FOREIGN KEY (`exam_status_id`) REFERENCES `exam_status` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO applicants VALUES(''3'',''9'',''20250001'',''applicant 1'',NULL,''test'',''09272521245'','''',''0000-00-00'','''',''BSIT'',''1st Year'',''registered'',''2025-02-14 18:29:37'',''2025-02-15 16:34:31'',NULL,NULL,''BS Information Technology'');
INSERT INTO applicants VALUES(''4'',''10'',''20250002'',''applicant 2'',NULL,''test'',''09272521245'','''',''0000-00-00'','''',''BSIT'',''1st Year'',''registered'',''2025-02-14 18:32:05'',''2025-02-15 16:34:31'',NULL,NULL,''BS Information Technology'');
INSERT INTO applicants VALUES(''5'',''11'',''20250003'',''applicant 3'',NULL,''test'',''09272521245'','''',''0000-00-00'','''',''BSCS'',''1st Year'',''registered'',''2025-02-14 18:34:16'',''2025-02-14 18:34:16'',NULL,NULL,''BS Computer Science'');
INSERT INTO applicants VALUES(''6'',''12'',''20250004'',''applicant 4'',NULL,''test'',''09272521245'','''',''0000-00-00'','''',''BSCS'',''1st Year'',''registered'',''2025-02-14 18:38:37'',''2025-02-14 18:38:37'',NULL,NULL,''BS Computer Science'');
INSERT INTO applicants VALUES(''7'',''13'',''20250005'',''applicant 5'',NULL,''test'',''09272521245'','''',''0000-00-00'','''',''BSIT'',''1st Year'',''registered'',''2025-02-14 18:43:12'',''2025-02-15 16:34:31'',NULL,NULL,''BS Information Technology'');
INSERT INTO applicants VALUES(''8'',''14'',''20250006'',''applicant 6'',NULL,''test'',''09272521245'','''',''0000-00-00'','''',''BSCS'',''1st Year'',''registered'',''2025-02-14 18:49:44'',''2025-02-14 18:49:44'',NULL,NULL,''BS Computer Science'');
INSERT INTO applicants VALUES(''9'',''15'',''20250007'',''applicant 7'',NULL,''test'',''09272521245'','''',''0000-00-00'','''',''BSIT'',''1st Year'',''registered'',''2025-02-14 18:55:38'',''2025-02-15 16:34:31'',NULL,NULL,''BS Information Technology'');
INSERT INTO applicants VALUES(''10'',''16'',''20250008'',''John Doe'',NULL,''Mamah'',''09651242386'','''',''0000-00-00'','''',''BSIT'',''1st Year'',''registered'',''2025-02-15 16:33:10'',''2025-02-15 16:33:10'',NULL,NULL,''BS Information Technology'');
INSERT INTO applicants VALUES(''11'',''17'',''20250009'',''Hwaryun'',NULL,''Guide'',''09876545665'',''Brgy. 772 Manila Philippines\r\n'',''0000-00-00'','''',''BSIT'',''1st Year'',''registered'',''2025-02-17 17:43:41'',''2025-02-17 17:59:25'',NULL,NULL,''BS Information Technology'');
INSERT INTO applicants VALUES(''12'',''19'',''20250010'',''Applicant'',NULL,''Test'',''09876543212'','''',''0000-00-00'','''',''BSCS'',''1st Year'',''registered'',''2025-02-17 18:50:29'',''2025-02-17 18:50:29'',NULL,NULL,''BS Computer Science'');


CREATE TABLE `application_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO application_status VALUES(''1'',''pending'',''Application is under review'',''2025-02-12 10:03:31'',''2025-02-12 10:03:31'');
INSERT INTO application_status VALUES(''2'',''approved'',''Application has been approved'',''2025-02-12 10:03:31'',''2025-02-12 10:03:31'');
INSERT INTO application_status VALUES(''3'',''rejected'',''Application has been rejected'',''2025-02-12 10:03:31'',''2025-02-12 10:03:31'');
INSERT INTO application_status VALUES(''4'',''incomplete'',''Application is missing required documents'',''2025-02-12 10:03:31'',''2025-02-12 10:03:31'');


CREATE TABLE `courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recipient_email` varchar(255) NOT NULL,
  `recipient_name` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `template_name` varchar(50) NOT NULL,
  `status` enum('sent','failed') NOT NULL,
  `error_message` text DEFAULT NULL,
  `related_type` enum('interview_schedule','interview_result','interview_reminder','interview_cancellation') NOT NULL,
  `related_id` int(11) NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `recipient_email` (`recipient_email`),
  KEY `template_name` (`template_name`),
  KEY `status` (`status`),
  KEY `related_type_id` (`related_type`,`related_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `exam_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `applicant_id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `passing_score` int(11) NOT NULL,
  `status` enum('pass','fail') NOT NULL,
  `started_at` datetime DEFAULT NULL,
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completion_time` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `applicant_id` (`applicant_id`),
  KEY `exam_id` (`exam_id`),
  CONSTRAINT `exam_results_ibfk_2` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`),
  CONSTRAINT `fk_exam_results_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO exam_results VALUES(''23'',''9'',''2'',''0'',''75'',''fail'',NULL,''2025-02-16 20:54:38'',NULL,''2025-02-16 20:54:38'');
INSERT INTO exam_results VALUES(''24'',''15'',''2'',''0'',''75'',''fail'',NULL,''2025-02-16 20:54:38'',NULL,''2025-02-16 20:54:38'');
INSERT INTO exam_results VALUES(''27'',''14'',''2'',''75'',''75'',''pass'',NULL,''2025-02-16 22:03:15'',NULL,''2025-02-16 22:03:15'');
INSERT INTO exam_results VALUES(''28'',''14'',''2'',''75'',''75'',''pass'',NULL,''2025-02-16 22:03:15'',NULL,''2025-02-16 22:03:15'');
INSERT INTO exam_results VALUES(''29'',''13'',''2'',''75'',''75'',''pass'',NULL,''2025-02-16 22:11:43'',NULL,''2025-02-16 22:11:43'');
INSERT INTO exam_results VALUES(''30'',''13'',''2'',''75'',''75'',''pass'',NULL,''2025-02-16 22:11:43'',NULL,''2025-02-16 22:11:43'');
INSERT INTO exam_results VALUES(''31'',''12'',''2'',''75'',''75'',''pass'',NULL,''2025-02-16 22:29:11'',NULL,''2025-02-16 22:29:11'');


CREATE TABLE `exam_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO exam_status VALUES(''1'',''not_started'',''Exam has not been started'',''2025-02-12 10:03:31'',''2025-02-12 10:03:31'');
INSERT INTO exam_status VALUES(''2'',''in_progress'',''Exam is currently in progress'',''2025-02-12 10:03:31'',''2025-02-12 10:03:31'');
INSERT INTO exam_status VALUES(''3'',''completed'',''Exam has been completed'',''2025-02-12 10:03:31'',''2025-02-12 10:03:31'');
INSERT INTO exam_status VALUES(''4'',''graded'',''Exam has been graded'',''2025-02-12 10:03:31'',''2025-02-12 10:03:31'');
INSERT INTO exam_status VALUES(''5'',''failed'',''Failed to meet the required score'',''2025-02-12 10:03:31'',''2025-02-12 10:03:31'');
INSERT INTO exam_status VALUES(''6'',''passed'',''Passed the required score'',''2025-02-12 10:03:31'',''2025-02-12 10:03:31'');


CREATE TABLE `exams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('mcq','coding') NOT NULL,
  `part` enum('1','2') NOT NULL,
  `duration_minutes` int(11) NOT NULL,
  `passing_score` int(11) NOT NULL,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `instructions` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `exams_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO exams VALUES(''1'',''test 1'',''Please be informed that the following exam will determine your eligibility for enrollment in the course. Kindly take it seriously. Thank you.'',''mcq'',''1'',''60'',''75'',''published'',''2'',''2025-02-14 14:42:04'',''2025-02-15 18:36:18'','''');
INSERT INTO exams VALUES(''2'',''Entrance Exam'',''Please be informed that the following exam will determine your eligibility for enrollment in the course. Kindly take it seriously. Thank you.'',''mcq'',''1'',''60'',''75'',''published'',''2'',''2025-02-14 14:42:13'',''2025-02-17 18:33:22'','''');


CREATE TABLE `interview_schedules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `applicant_id` int(11) NOT NULL,
  `interviewer_id` int(11) NOT NULL,
  `schedule_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('scheduled','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `notes` text DEFAULT NULL,
  `meeting_link` varchar(255) DEFAULT NULL,
  `interview_status` enum('pending','passed','failed') NOT NULL DEFAULT 'pending',
  `total_score` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `applicant_id` (`applicant_id`),
  KEY `interviewer_id` (`interviewer_id`),
  CONSTRAINT `interview_schedules_ibfk_1` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`),
  CONSTRAINT `interview_schedules_ibfk_2` FOREIGN KEY (`interviewer_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `interview_scores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `interview_id` int(11) NOT NULL,
  `category` enum('technical_skills','communication','problem_solving','cultural_fit','overall_impression') NOT NULL,
  `score` int(11) NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `interview_id` (`interview_id`),
  CONSTRAINT `interview_scores_ibfk_1` FOREIGN KEY (`interview_id`) REFERENCES `interview_schedules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `question_type` enum('multiple_choice','coding') NOT NULL,
  `points` int(11) NOT NULL DEFAULT 1,
  `coding_template` text DEFAULT NULL,
  `test_cases` text DEFAULT NULL,
  `solution` text DEFAULT NULL,
  `explanation` text DEFAULT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
  `correct_answer` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `exam_id` (`exam_id`),
  CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO questions VALUES(''1'',''2'',''What is the size of maaah pet cock'',''multiple_choice'',''15'',NULL,NULL,NULL,''Correct Answer is A cause its a wiener you cuck sucker'',''[\"12\",\"32\",\"12\",\"12\"]'',''0'',''2025-02-14 14:44:11'',''2025-02-14 14:44:11'');
INSERT INTO questions VALUES(''2'',''2'',''test'',''multiple_choice'',''15'',NULL,NULL,NULL,'''',''[\"qw\",\"qwe\",\"qweq\",\"weq\"]'',''0'',''2025-02-14 14:44:42'',''2025-02-14 14:44:42'');
INSERT INTO questions VALUES(''3'',''2'',''7t7tt7ttuuttutu'',''multiple_choice'',''15'',NULL,NULL,NULL,'''',''[\"456\",\"456\",\"4564\",\"456\"]'',''0'',''2025-02-14 14:44:57'',''2025-02-14 14:44:57'');
INSERT INTO questions VALUES(''4'',''2'',''234'',''multiple_choice'',''15'',NULL,NULL,NULL,''qwe'',''[\"12\",\"sdr\",\"wer\",\"wer\"]'',''0'',''2025-02-14 14:45:10'',''2025-02-14 14:45:10'');
INSERT INTO questions VALUES(''5'',''2'',''qweq'',''multiple_choice'',''15'',NULL,NULL,NULL,''asda'',''[\"123\",\"1231\",\"2312\",\"3123\"]'',''0'',''2025-02-14 14:45:41'',''2025-02-14 14:45:41'');
INSERT INTO questions VALUES(''6'',''1'',''123123'',''multiple_choice'',''50'',NULL,NULL,NULL,'''',''[\"123123\",\"123\",\"123123\",\"123123\"]'',''0'',''2025-02-15 19:28:07'',''2025-02-15 19:28:07'');
INSERT INTO questions VALUES(''7'',''1'',''123212'',''multiple_choice'',''50'',NULL,NULL,NULL,'''',''[\"123\",\"12312\",\"3123\",\"1231\"]'',''0'',''2025-02-15 19:28:18'',''2025-02-15 19:28:18'');


CREATE TABLE `status_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `status_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `status_history_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO status_history VALUES(''1'',''15'',''approved'','''',''5'',''2025-02-15 15:15:27'');
INSERT INTO status_history VALUES(''2'',''16'',''rejected'','''',''5'',''2025-02-15 17:16:29'');
INSERT INTO status_history VALUES(''3'',''14'',''pending'','''',''5'',''2025-02-15 17:43:11'');
INSERT INTO status_history VALUES(''4'',''14'',''approved'','''',''5'',''2025-02-15 17:44:23'');
INSERT INTO status_history VALUES(''5'',''13'',''approved'','''',''5'',''2025-02-16 22:10:18'');
INSERT INTO status_history VALUES(''6'',''12'',''approved'','''',''5'',''2025-02-16 22:10:40'');
INSERT INTO status_history VALUES(''7'',''11'',''approved'','''',''5'',''2025-02-16 22:10:42'');
INSERT INTO status_history VALUES(''8'',''10'',''approved'','''',''5'',''2025-02-16 22:10:46'');
INSERT INTO status_history VALUES(''9'',''9'',''approved'','''',''5'',''2025-02-16 22:11:05'');
INSERT INTO status_history VALUES(''10'',''17'',''pending'','''',''18'',''2025-02-17 17:52:46'');
INSERT INTO status_history VALUES(''11'',''17'',''approved'','''',''18'',''2025-02-17 17:52:56'');
INSERT INTO status_history VALUES(''12'',''19'',''approved'','''',''18'',''2025-02-17 18:52:20'');


CREATE TABLE `super_admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `super_admins_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO super_admins VALUES(''2'',''2'',''Carlo Joshua'',''Abellera'');


CREATE TABLE `support_tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('open','in_progress','closed') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `support_tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `ticket_responses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `ticket_responses_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`),
  CONSTRAINT `ticket_responses_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `role` enum('super_admin','admin','applicant') NOT NULL,
  `status` enum('active','inactive','pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO users VALUES(''2'',''superadmin1@ccs.com'',''$2y$10$GutilQeoQQlOqddBN.eOcuz2JDM4Xw8CGJMaTV8ANHBfms7OGiqfG'',''Carlo Joshua'',''Abellera'',''super_admin'',''active'',''2025-02-12 10:50:16'',''2025-02-17 17:23:22'',NULL);
INSERT INTO users VALUES(''5'',''test_admin@gmail.com'',''$2y$10$im1kdvuom2.t/ku3opOvHOBG7fNdSngM4XOdSMFiX8GDPWgqcp/se'','''','''',''admin'',''active'',''2025-02-14 14:06:03'',''2025-02-14 14:06:03'',NULL);
INSERT INTO users VALUES(''9'',''testingapplicant@gmail.com'',''$2y$10$t8w2EU/cdswTUrV2wbKRTeMgbuGf2uwEdo4gmZAIwVTjElllIVSNS'','''','''',''applicant'',''approved'',''2025-02-14 18:29:37'',''2025-02-16 22:11:05'',''5'');
INSERT INTO users VALUES(''10'',''testingapplicant2@gmail.com'',''$2y$10$KF99MANxSdg6MglMMKXKJenkRIhjyTrZtYmefE.tpdTRRs.nKkpcW'','''','''',''applicant'',''approved'',''2025-02-14 18:32:05'',''2025-02-16 22:10:46'',''5'');
INSERT INTO users VALUES(''11'',''testingapplicant3@gmail.com'',''$2y$10$K84gqSvABeAhWLCeHRaPiu/VTzwfyDASfkp/zEjQf2gD/9biHFR4q'','''','''',''applicant'',''approved'',''2025-02-14 18:34:16'',''2025-02-16 22:10:42'',''5'');
INSERT INTO users VALUES(''12'',''testingapplicant4@gmail.com'',''$2y$10$yta6GEhZyGWplgXkrm6RTeuK1AuirVxwr2sjqrHCW7kAF29JS/Ud6'','''','''',''applicant'',''approved'',''2025-02-14 18:38:37'',''2025-02-16 22:10:39'',''5'');
INSERT INTO users VALUES(''13'',''testingapplicant5@gmail.com'',''$2y$10$PK/e4PgeWel2Pvbte2D84ejd9KkMvwJSCopS/YsQvcRTjfVvM4HuC'','''','''',''applicant'',''approved'',''2025-02-14 18:43:12'',''2025-02-16 22:10:18'',''5'');
INSERT INTO users VALUES(''14'',''testingapplicant6@gmail.com'',''$2y$10$iiDQuEDvbWoR0QcRIXhIi.TrfIO1NMCAC0mtayWlGx4p.A.5IyGxa'','''','''',''applicant'',''approved'',''2025-02-14 18:49:44'',''2025-02-15 17:44:23'',''5'');
INSERT INTO users VALUES(''15'',''testingapplicant7@gmail.com'',''$2y$10$4U6Xp.i5SMwPtXu0tMv9guKwbd8SSA8ONwyTyAl4QoaD4NUx4xFH.'','''','''',''applicant'',''approved'',''2025-02-14 18:55:38'',''2025-02-15 17:34:10'',''5'');
INSERT INTO users VALUES(''16'',''john_doe.mama@gmail.com'',''$2y$10$QesfjKh7Xdd6P6BWO96ACegaOitFDLyxl6AheQZR3/ykoYXOstsIK'','''','''',''applicant'',''rejected'',''2025-02-15 16:33:10'',''2025-02-15 17:16:29'',''5'');
INSERT INTO users VALUES(''17'',''hwaryun@gmail.com'',''$2y$10$2SVzU9J67G1f9tKIvNaARenqlRnv9wrhV9uSJEAc8MWSypmRWXdXy'','''','''',''applicant'',''approved'',''2025-02-17 17:43:41'',''2025-02-17 17:52:56'',''18'');
INSERT INTO users VALUES(''18'',''hajinsung@gmail.com'',''$2y$10$FtqLQ6fy008LXrDCB88enuLQGOx1eYgtOexo5WXXQF8W34X3jzBZa'','''','''',''admin'',''active'',''2025-02-17 17:48:53'',''2025-02-17 17:48:53'',NULL);
INSERT INTO users VALUES(''19'',''applicanttest@gmail.com'',''$2y$10$Aq1PiGzZDhJp/Uuhsl8D7.8yMRYd0reTzDexo2Rh6kBjHtgCU8XxS'','''','''',''applicant'',''approved'',''2025-02-17 18:50:29'',''2025-02-17 18:52:20'',''18'');
