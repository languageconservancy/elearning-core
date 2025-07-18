-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Jul 18, 2025 at 11:05 PM
-- Server version: 5.7.39
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `elearning_demo_db`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `UnitReview` (IN `userId` INT, IN `UnitId` INT)  NO SQL BEGIN
	DECLARE done BOOLEAN DEFAULT FALSE;
	DECLARE reding_numerator INT DEFAULT 0;
	DECLARE writing_numerator INT DEFAULT 0;
	DECLARE speaking_numerator INT DEFAULT 0;
	DECLARE listening_numerator INT DEFAULT 0;
	DECLARE all_numerator INT DEFAULT 0;
	DECLARE skilltype VARCHAR(25);
	DECLARE xp1 INT DEFAULT 0;
	DECLARE xp2 INT DEFAULT 0;
	DECLARE xp3 INT DEFAULT 0;
	DECLARE xp4 INT DEFAULT 0;
	DECLARE cardCounter INT DEFAULT 0;
	DECLARE cur CURSOR FOR SELECT xp_1,xp_2,xp_3,xp_4,skill_type FROM review_queues WHERE user_id = userId AND card_id IN (SELECT card_id FROM card_units WHERE unit_id = UnitId);
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done =TRUE;

	SELECT COUNT(card_id) INTO cardCounter FROM card_units WHERE unit_id = UnitId;
	OPEN cur;
		cur_loop:LOOP
			FETCH NEXT FROM cur INTO xp1,xp2,xp3,xp4,skilltype;
			IF done THEN
				LEAVE cur_loop;
			END IF;

			IF ((xp1>0 OR  xp2>0 OR xp3>0 OR xp4>0) AND (skilltype='reading'))  THEN
				SET reding_numerator=reding_numerator+1;
			END IF;

			IF ((xp1>0 OR  xp2>0 OR xp3>0 OR xp4>0) AND (skilltype='writing'))  THEN
				SET writing_numerator=writing_numerator+1;
			END IF;

			IF ((xp1>0 OR  xp2>0 OR xp3>0 OR xp4>0) AND (skilltype='speaking'))  THEN
				SET speaking_numerator=speaking_numerator+1;
			END IF;

			IF ((xp1>0 OR  xp2>0 OR xp3>0 OR xp4>0) AND (skilltype='listening'))  THEN
				SET listening_numerator=listening_numerator+1;
			END IF;

		END LOOP;
	CLOSE cur;
		SET all_numerator= listening_numerator + speaking_numerator + writing_numerator+ reding_numerator;
		IF exists (select * from `unit_fires` where user_id = userId and unit_id=UnitId) Then
			UPDATE `unit_fires` SET `reading_persantage`=(reding_numerator*100/cardCounter), `writing_percentage`=(writing_numerator*100/cardCounter), `listening_percentage`=(listening_numerator*100/cardCounter), `speaking_percentage`=(speaking_numerator*100/cardCounter), `all_persentage`=(all_numerator*100/(cardCounter*4)) WHERE  `user_id`=userId AND `unit_id`=UnitId;
		else
			INSERT INTO `unit_fires` (`user_id`, `unit_id`, `reading_persantage`, `writing_percentage`, `listening_percentage`, `speaking_percentage`,`all_persentage`) VALUES (userId, UnitId, (reding_numerator*100/cardCounter), (writing_numerator*100/cardCounter),(listening_numerator*100/cardCounter), (speaking_numerator*100/cardCounter), (all_numerator*100/(cardCounter*4)));
		END IF;
 END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `activity_types`
--

CREATE TABLE `activity_types` (
  `id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `specific_skill` enum('reading','listening (comprehension)','listening (phonemic awareness)','phonemic','listening / spelling','spelling') NOT NULL COMMENT 'Skill type according to Jan''s document on google drive',
  `global_skill` enum('reading','listening','writing','speaking') NOT NULL COMMENT 'Skill type in software, since these are what exist in the database, like in ReviewQueues table',
  `prompt_response_pairs_words` set('a-i','a-l','a-e','i-a','i-l','i-e','l-a','l-i','l-e','e-a','e-i','e-l') NOT NULL COMMENT 'Which prompt-response pairs are valid for the activity type',
  `prompt_response_pairs_patterns` set('a-i','a-l','a-e','i-a','i-l','i-e','l-a','l-i','l-e','e-a','e-i','e-l') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `exercise_type_words` set('match-the-pair','anagram','fill_in_the_blanks_typing','fill_in_the_blanks_mcq','Word Search','Crossword','truefalse','multiple-choice','recording','Type Answer','Rearrange words','Building Blocks','Word Fill (Type)','Word Fill (Multiple Choice)') NOT NULL COMMENT 'Which exercise types are allowed for the activity type. These must correspond to point references table''s exercise column',
  `exercise_type_patterns` set('match-the-pair','multiple-choice','Word Fill (Multiple Choice)','Building Blocks','Type Answer','Word Fill (Type)') NOT NULL COMMENT 'Exercise types for Pattern card activities',
  `learning_percentage_words` int(11) NOT NULL COMMENT 'Percentage of word cards that will be presented to the user using this activity type during learning session exercises',
  `learning_percentage_patterns` int(11) NOT NULL COMMENT 'Percentage of pattern cards that will be presented to the user using this activity type during learning session exercise',
  `review_percentage_words` int(11) NOT NULL COMMENT 'Percentage of word cards that will be presented to the user using this activity type during review session exercises',
  `review_percentage_patterns` int(11) NOT NULL COMMENT 'Percentage of pattern cards that will be presented to the user using this activity type during review session exercises',
  `learning_style` enum('passive','active','active_aided','passive_to_active') NOT NULL COMMENT 'Style of learning that this activity involves. Active_aided implies a combo, passive_to_active implies passive prompt with active response',
  `exclude_words` bit(1) NOT NULL DEFAULT b'0' COMMENT 'Whether the activity type for word cards should be excluded from the algorithm that uses it',
  `exclude_patterns` bit(1) NOT NULL DEFAULT b'0' COMMENT 'Whether the activity type for pattern cards should be excluded from the algorithm that uses it'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `activity_types`
--

INSERT INTO `activity_types` (`id`, `type`, `specific_skill`, `global_skill`, `prompt_response_pairs_words`, `prompt_response_pairs_patterns`, `exercise_type_words`, `exercise_type_patterns`, `learning_percentage_words`, `learning_percentage_patterns`, `review_percentage_words`, `review_percentage_patterns`, `learning_style`, `exclude_words`, `exclude_patterns`) VALUES
(1, 1, 'reading', 'reading', 'l-i,l-e', 'l-e', 'match-the-pair', 'match-the-pair', 100, 100, 25, 50, 'passive', b'0', b'0'),
(2, 1, 'reading', 'reading', 'l-i,l-e', 'l-i,l-e', 'multiple-choice', 'multiple-choice', 100, 100, 25, 50, 'passive', b'0', b'0'),
(3, 2, 'reading', 'reading', 'i-l,e-l', '', 'match-the-pair', '', 100, 100, 25, 0, 'passive', b'0', b'1'),
(4, 2, 'reading', 'reading', 'i-l,e-l', 'i-l,e-l', 'multiple-choice', 'multiple-choice', 100, 100, 25, 50, 'passive', b'0', b'0'),
(5, 3, 'listening (comprehension)', 'listening', 'a-e', '', 'match-the-pair', '', 50, 50, 50, 0, 'passive', b'0', b'1'),
(6, 3, 'listening (comprehension)', 'listening', 'a-e', 'a-e', 'multiple-choice', 'multiple-choice', 50, 50, 50, 20, 'passive', b'0', b'0'),
(7, 4, 'listening (phonemic awareness)', 'listening', 'a-l', '', 'match-the-pair', '', 25, 25, 15, 0, 'passive', b'0', b'1'),
(8, 4, 'listening (phonemic awareness)', 'listening', 'a-l', 'a-l', 'multiple-choice', 'multiple-choice', 25, 25, 15, 15, 'passive', b'0', b'0'),
(9, 5, 'phonemic', 'listening', 'a-l', 'a-l', 'fill_in_the_blanks_typing', 'Word Fill (Multiple Choice)', 2, 0, 100, 100, 'passive_to_active', b'0', b'0'),
(10, 5, 'phonemic', 'listening', 'a-l', 'a-l', 'fill_in_the_blanks_mcq', 'Word Fill (Type)', 2, 0, 100, 100, 'passive_to_active', b'0', b'0'),
(11, 6, 'phonemic', 'listening', 'a-l', 'a-l', 'anagram', 'Building Blocks', 25, 25, 15, 15, 'passive_to_active', b'0', b'1'),
(12, 7, 'listening / spelling', 'listening', 'a-l', 'a-l', 'Type Answer', 'Type Answer', 1, 0, 1, 0, 'passive', b'1', b'1'),
(13, 8, 'spelling', 'writing', 'e-l', 'e-l', 'anagram', 'Building Blocks', 30, 30, 30, 30, 'active_aided', b'0', b'1'),
(14, 9, 'spelling', 'writing', 'e-l', 'e-l', 'Type Answer', 'Type Answer', 5, 0, 5, 0, 'active', b'1', b'1');

-- --------------------------------------------------------

--
-- Table structure for table `banned_words`
--

CREATE TABLE `banned_words` (
  `id` int(11) NOT NULL,
  `word` varchar(255) NOT NULL,
  `isolated_only` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `banned_words`
--

INSERT INTO `banned_words` (`id`, `word`, `isolated_only`) VALUES
(1, '4r5e', 0),
(2, '5h1t', 0),
(3, '5hit', 0),
(4, 'a55', 0),
(5, 'anal', 0),
(6, 'anus', 0),
(7, 'ar5e', 0),
(8, 'arrse', 0),
(9, 'arse', 0),
(10, 'ass', 1),
(11, 'ass-fucker', 0),
(12, 'asses', 0),
(13, 'assfucker', 0),
(14, 'assfukka', 0),
(15, 'asshole', 0),
(16, 'assholes', 0),
(17, 'asswhole', 0),
(18, 'a_s_s', 0),
(19, 'b!tch', 0),
(20, 'b00bs', 0),
(21, 'b17ch', 0),
(22, 'b1tch', 0),
(23, 'ballbag', 0),
(24, 'balls', 0),
(25, 'ballsack', 0),
(26, 'bastard', 0),
(27, 'beastial', 0),
(28, 'beastiality', 0),
(29, 'bellend', 0),
(30, 'bestial', 0),
(31, 'bestiality', 0),
(32, 'bi+ch', 0),
(33, 'biatch', 0),
(34, 'bitch', 0),
(35, 'bitcher', 0),
(36, 'bitchers', 0),
(37, 'bitches', 0),
(38, 'bitchin', 0),
(39, 'bitching', 0),
(40, 'bloody', 0),
(41, 'blow job', 0),
(42, 'blowjob', 0),
(43, 'blowjobs', 0),
(44, 'boiolas', 0),
(45, 'bollock', 0),
(46, 'bollok', 0),
(47, 'boner', 0),
(48, 'boob', 0),
(49, 'boobs', 0),
(50, 'booobs', 0),
(51, 'boooobs', 0),
(52, 'booooobs', 0),
(53, 'booooooobs', 0),
(54, 'breasts', 0),
(55, 'buceta', 0),
(56, 'bugger', 0),
(57, 'bum', 1),
(58, 'bunny fucker', 0),
(59, 'butt', 0),
(60, 'butthole', 0),
(61, 'buttmuch', 0),
(62, 'buttplug', 0),
(63, 'c0ck', 0),
(64, 'c0cksucker', 0),
(65, 'carpet muncher', 0),
(66, 'cawk', 0),
(67, 'chink', 0),
(68, 'cipa', 0),
(69, 'cl1t', 0),
(70, 'clit', 0),
(71, 'clitoris', 0),
(72, 'clits', 0),
(73, 'cnut', 0),
(74, 'cock', 0),
(75, 'cock-sucker', 0),
(76, 'cockface', 0),
(77, 'cockhead', 0),
(78, 'cockmunch', 0),
(79, 'cockmuncher', 0),
(80, 'cocks', 0),
(81, 'cocksuck', 0),
(82, 'cocksucked', 0),
(83, 'cocksucker', 0),
(84, 'cocksucking', 0),
(85, 'cocksucks', 0),
(86, 'cocksuka', 0),
(87, 'cocksukka', 0),
(88, 'cok', 0),
(89, 'cokmuncher', 0),
(90, 'coksucka', 0),
(91, 'coon', 0),
(92, 'cox', 0),
(93, 'crap', 0),
(94, 'cum', 0),
(95, 'cummer', 0),
(96, 'cumming', 0),
(97, 'cums', 0),
(98, 'cumshot', 0),
(99, 'cunilingus', 0),
(100, 'cunillingus', 0),
(101, 'cunnilingus', 0),
(102, 'cunt', 0),
(103, 'cuntlick', 0),
(104, 'cuntlicker', 0),
(105, 'cuntlicking', 0),
(106, 'cunts', 0),
(107, 'cyalis', 0),
(108, 'cyberfuc', 0),
(109, 'cyberfuck', 0),
(110, 'cyberfucked', 0),
(111, 'cyberfucker', 0),
(112, 'cyberfuckers', 0),
(113, 'cyberfucking', 0),
(114, 'd1ck', 0),
(115, 'damn', 0),
(116, 'dick', 0),
(117, 'dickhead', 0),
(118, 'dildo', 0),
(119, 'dildos', 0),
(120, 'dink', 0),
(121, 'dinks', 0),
(122, 'dirsa', 0),
(123, 'dlck', 0),
(124, 'dog-fucker', 0),
(125, 'doggin', 0),
(126, 'dogging', 0),
(127, 'donkeyribber', 0),
(128, 'doosh', 0),
(129, 'duche', 0),
(130, 'dyke', 0),
(131, 'ejaculate', 0),
(132, 'ejaculated', 0),
(133, 'ejaculates', 0),
(134, 'ejaculating', 0),
(135, 'ejaculatings', 0),
(136, 'ejaculation', 0),
(137, 'ejakulate', 0),
(138, 'f u c k', 0),
(139, 'f u c k e r', 0),
(140, 'f4nny', 0),
(141, 'fag', 0),
(142, 'fagging', 0),
(143, 'faggitt', 0),
(144, 'faggot', 0),
(145, 'faggs', 0),
(146, 'fagot', 0),
(147, 'fagots', 0),
(148, 'fags', 0),
(149, 'fanny', 0),
(150, 'fannyflaps', 0),
(151, 'fannyfucker', 0),
(152, 'fanyy', 0),
(153, 'fatass', 0),
(154, 'fcuk', 0),
(155, 'fcuker', 0),
(156, 'fcuking', 0),
(157, 'feck', 0),
(158, 'fecker', 0),
(159, 'felching', 0),
(160, 'fellate', 0),
(161, 'fellatio', 0),
(162, 'fingerfuck', 0),
(163, 'fingerfucked', 0),
(164, 'fingerfucker', 0),
(165, 'fingerfuckers', 0),
(166, 'fingerfucking', 0),
(167, 'fingerfucks', 0),
(168, 'fistfuck', 0),
(169, 'fistfucked', 0),
(170, 'fistfucker', 0),
(171, 'fistfuckers', 0),
(172, 'fistfucking', 0),
(173, 'fistfuckings', 0),
(174, 'fistfucks', 0),
(175, 'flange', 0),
(176, 'fook', 0),
(177, 'fooker', 0),
(178, 'fuck', 0),
(179, 'fucka', 0),
(180, 'fucked', 0),
(181, 'fucker', 0),
(182, 'fuckers', 0),
(183, 'fuckhead', 0),
(184, 'fuckheads', 0),
(185, 'fuckin', 0),
(186, 'fucking', 0),
(187, 'fuckings', 0),
(188, 'fuckingshitmotherfucker', 0),
(189, 'fuckme', 0),
(190, 'fucks', 0),
(191, 'fuckwhit', 0),
(192, 'fuckwit', 0),
(193, 'fudge packer', 0),
(194, 'fudgepacker', 0),
(195, 'fuk', 0),
(196, 'fuker', 0),
(197, 'fukker', 0),
(198, 'fukkin', 0),
(199, 'fuks', 0),
(200, 'fukwhit', 0),
(201, 'fukwit', 0),
(202, 'fux', 0),
(203, 'fux0r', 0),
(204, 'f_u_c_k', 0),
(205, 'gangbang', 0),
(206, 'gangbanged', 0),
(207, 'gangbangs', 0),
(208, 'gaylord', 0),
(209, 'gaysex', 0),
(210, 'goatse', 0),
(211, 'god-dam', 0),
(212, 'god-damned', 0),
(213, 'goddamn', 0),
(214, 'goddamned', 0),
(215, 'hardcoresex', 0),
(216, 'hell', 1),
(217, 'heshe', 0),
(218, 'hoar', 0),
(219, 'hoare', 0),
(220, 'hoer', 0),
(221, 'homo', 1),
(222, 'hore', 0),
(223, 'horniest', 0),
(224, 'horny', 0),
(225, 'hotsex', 0),
(226, 'jack-off', 0),
(227, 'jackoff', 0),
(228, 'jap', 0),
(229, 'jerk-off', 0),
(230, 'jism', 0),
(231, 'jiz', 0),
(232, 'jizm', 0),
(233, 'jizz', 0),
(234, 'kawk', 0),
(235, 'knob', 0),
(236, 'knobead', 0),
(237, 'knobed', 0),
(238, 'knobend', 0),
(239, 'knobhead', 0),
(240, 'knobjocky', 0),
(241, 'knobjokey', 0),
(242, 'kock', 0),
(243, 'kondum', 0),
(244, 'kondums', 0),
(245, 'kum', 0),
(246, 'kummer', 0),
(247, 'kumming', 0),
(248, 'kums', 0),
(249, 'kunilingus', 0),
(250, 'l3i+ch', 0),
(251, 'l3itch', 0),
(252, 'labia', 0),
(253, 'lmfao', 0),
(254, 'lust', 0),
(255, 'lusting', 0),
(256, 'm0f0', 0),
(257, 'm0fo', 0),
(258, 'm45terbate', 0),
(259, 'ma5terb8', 0),
(260, 'ma5terbate', 0),
(261, 'masochist', 0),
(262, 'master-bate', 0),
(263, 'masterb8', 0),
(264, 'masterbat', 0),
(265, 'masterbat3', 0),
(266, 'masterbate', 0),
(267, 'masterbation', 0),
(268, 'masterbations', 0),
(269, 'masturbate', 0),
(270, 'mo-fo', 0),
(271, 'mof0', 0),
(272, 'mofo', 0),
(273, 'mothafuck', 0),
(274, 'mothafucka', 0),
(275, 'mothafuckas', 0),
(276, 'mothafuckaz', 0),
(277, 'mothafucked', 0),
(278, 'mothafucker', 0),
(279, 'mothafuckers', 0),
(280, 'mothafuckin', 0),
(281, 'mothafucking', 0),
(282, 'mothafuckings', 0),
(283, 'mothafucks', 0),
(284, 'mother fucker', 0),
(285, 'motherfuck', 0),
(286, 'motherfucked', 0),
(287, 'motherfucker', 0),
(288, 'motherfuckers', 0),
(289, 'motherfuckin', 0),
(290, 'motherfucking', 0),
(291, 'motherfuckings', 0),
(292, 'motherfuckka', 0),
(293, 'motherfucks', 0),
(294, 'muff', 0),
(295, 'mutha', 0),
(296, 'muthafecker', 0),
(297, 'muthafuckker', 0),
(298, 'muther', 0),
(299, 'mutherfucker', 0),
(300, 'n1gga', 0),
(301, 'n1gger', 0),
(302, 'nazi', 0),
(303, 'nigg3r', 0),
(304, 'nigg4h', 0),
(305, 'nigga', 0),
(306, 'niggah', 0),
(307, 'niggas', 0),
(308, 'niggaz', 0),
(309, 'nigger', 0),
(310, 'niggers', 0),
(311, 'nob', 1),
(312, 'nob jokey', 0),
(313, 'nobhead', 0),
(314, 'nobjocky', 0),
(315, 'nobjokey', 0),
(316, 'numbnuts', 0),
(317, 'nutsack', 0),
(318, 'orgasim', 0),
(319, 'orgasims', 0),
(320, 'orgasm', 0),
(321, 'orgasms', 0),
(322, 'p0rn', 0),
(323, 'pawn', 0),
(324, 'pecker', 0),
(325, 'penis', 0),
(326, 'penisfucker', 0),
(327, 'phonesex', 0),
(328, 'phuck', 0),
(329, 'phuk', 0),
(330, 'phuked', 0),
(331, 'phuking', 0),
(332, 'phukked', 0),
(333, 'phukking', 0),
(334, 'phuks', 0),
(335, 'phuq', 0),
(336, 'pigfucker', 0),
(337, 'pimpis', 0),
(338, 'piss', 0),
(339, 'pissed', 0),
(340, 'pisser', 0),
(341, 'pissers', 0),
(342, 'pisses', 0),
(343, 'pissflaps', 0),
(344, 'pissin', 0),
(345, 'pissing', 0),
(346, 'pissoff', 0),
(347, 'poop', 0),
(348, 'porn', 0),
(349, 'porno', 0),
(350, 'pornography', 0),
(351, 'pornos', 0),
(352, 'prick', 0),
(353, 'pricks', 0),
(354, 'pron', 0),
(355, 'pube', 0),
(356, 'pusse', 0),
(357, 'pussi', 0),
(358, 'pussies', 0),
(359, 'pussy', 0),
(360, 'pussys', 0),
(361, 'rectum', 0),
(362, 'retard', 0),
(363, 'rimjaw', 0),
(364, 'rimming', 0),
(365, 's hit', 0),
(366, 's.o.b.', 0),
(367, 'sadist', 0),
(368, 'schlong', 0),
(369, 'screwing', 0),
(370, 'scroat', 0),
(371, 'scrote', 0),
(372, 'scrotum', 0),
(373, 'semen', 0),
(374, 'sex', 0),
(375, 'sh!+', 0),
(376, 'sh!t', 0),
(377, 'sh1t', 0),
(378, 'shag', 0),
(379, 'shagger', 0),
(380, 'shaggin', 0),
(381, 'shagging', 0),
(382, 'shemale', 0),
(383, 'shi+', 0),
(384, 'shit', 0),
(385, 'shitdick', 0),
(386, 'shite', 0),
(387, 'shited', 0),
(388, 'shitey', 0),
(389, 'shitfuck', 0),
(390, 'shitfull', 0),
(391, 'shithead', 0),
(392, 'shiting', 0),
(393, 'shitings', 0),
(394, 'shits', 0),
(395, 'shitted', 0),
(396, 'shitter', 0),
(397, 'shitters', 0),
(398, 'shitting', 0),
(399, 'shittings', 0),
(400, 'shitty', 0),
(401, 'skank', 0),
(402, 'slut', 0),
(403, 'sluts', 0),
(404, 'smegma', 0),
(405, 'smut', 0),
(406, 'snatch', 0),
(407, 'son-of-a-bitch', 0),
(408, 'spac', 1),
(409, 'spunk', 0),
(410, 's_h_i_t', 0),
(411, 't1tt1e5', 0),
(412, 't1tties', 0),
(413, 'teets', 0),
(414, 'teez', 0),
(415, 'testical', 0),
(416, 'testicle', 0),
(417, 'tit', 1),
(418, 'titfuck', 0),
(419, 'tits', 0),
(420, 'titt', 0),
(421, 'tittie5', 0),
(422, 'tittiefucker', 0),
(423, 'titties', 0),
(424, 'tittyfuck', 0),
(425, 'tittywank', 0),
(426, 'titwank', 0),
(427, 'tosser', 0),
(428, 'turd', 0),
(429, 'tw4t', 0),
(430, 'twat', 0),
(431, 'twathead', 0),
(432, 'twatty', 0),
(433, 'twunt', 0),
(434, 'twunter', 0),
(435, 'v14gra', 0),
(436, 'v1gra', 0),
(437, 'vagina', 0),
(438, 'viagra', 0),
(439, 'vulva', 0),
(440, 'w00se', 0),
(441, 'wang', 1),
(442, 'wank', 0),
(443, 'wanker', 0),
(444, 'wanky', 0),
(445, 'whoar', 0),
(446, 'whore', 0),
(447, 'willies', 0),
(448, 'willy', 0),
(449, 'xrated', 0),
(450, 'xxx', 0),
(451, 'arsehole', 0),
(452, 'assbag', 0),
(453, 'assbandit', 0),
(454, 'assbanger', 0),
(455, 'assbite', 0),
(456, 'assclown', 0),
(457, 'asscock', 0),
(458, 'asscracker', 0),
(459, 'assface', 0),
(460, 'assfuck', 0),
(461, 'assgoblin', 0),
(462, 'asshat', 0),
(463, 'ass-hat', 0),
(464, 'asshead', 0),
(465, 'asshopper', 0),
(466, 'ass-jabber', 0),
(467, 'assjacker', 0),
(468, 'asslick', 0),
(469, 'asslicker', 0),
(470, 'assmonkey', 0),
(471, 'assmunch', 0),
(472, 'assmuncher', 0),
(473, 'assnigger', 0),
(474, 'asspirate', 0),
(475, 'ass-pirate', 0),
(476, 'assshit', 0),
(477, 'assshole', 0),
(478, 'asssucker', 0),
(479, 'asswad', 0),
(480, 'asswipe', 0),
(481, 'axwound', 0),
(482, 'bampot', 0),
(483, 'beaner', 0),
(484, 'bitchass', 0),
(485, 'bitchtits', 0),
(486, 'bitchy', 0),
(487, 'bollocks', 0),
(488, 'bollox', 0),
(489, 'brotherfucker', 0),
(490, 'bullshit', 0),
(491, 'bumblefuck', 0),
(492, 'butt plug', 0),
(493, 'buttfucka', 0),
(494, 'butt-pirate', 0),
(495, 'buttfucker', 0),
(496, 'camel toe', 0),
(497, 'carpetmuncher', 0),
(498, 'chesticle', 0),
(499, 'chinc', 0),
(500, 'choad', 0),
(501, 'chode', 0),
(502, 'clitface', 0),
(503, 'clitfuck', 0),
(504, 'clusterfuck', 0),
(505, 'cockass', 0),
(506, 'cockbite', 0),
(507, 'cockburger', 0),
(508, 'cockfucker', 0),
(509, 'cockjockey', 0),
(510, 'cockknoker', 0),
(511, 'cockmaster', 0),
(512, 'cockmongler', 0),
(513, 'cockmongruel', 0),
(514, 'cockmonkey', 0),
(515, 'cocknose', 0),
(516, 'cocknugget', 0),
(517, 'cockshit', 0),
(518, 'cocksmith', 0),
(519, 'cocksmoke', 0),
(520, 'cocksmoker', 0),
(521, 'cocksniffer', 0),
(522, 'cockwaffle', 0),
(523, 'coochie', 0),
(524, 'coochy', 0),
(525, 'cooter', 0),
(526, 'cracker', 0),
(527, 'cumbubble', 0),
(528, 'cumdumpster', 0),
(529, 'cumguzzler', 0),
(530, 'cumjockey', 0),
(531, 'cumslut', 0),
(532, 'cumtart', 0),
(533, 'cunnie', 0),
(534, 'cuntass', 0),
(535, 'cuntface', 0),
(536, 'cunthole', 0),
(537, 'cuntrag', 0),
(538, 'cuntslut', 0),
(539, 'dago', 0),
(540, 'deggo', 0),
(541, 'dickbag', 0),
(542, 'dickbeaters', 0),
(543, 'dickface', 0),
(544, 'dickfuck', 0),
(545, 'dickfucker', 0),
(546, 'dickhole', 0),
(547, 'dickjuice', 0),
(548, 'dickmilk ', 0),
(549, 'dickmonger', 0),
(550, 'dicks', 0),
(551, 'dickslap', 0),
(552, 'dick-sneeze', 0),
(553, 'dicksucker', 0),
(554, 'dicksucking', 0),
(555, 'dicktickler', 0),
(556, 'dickwad', 0),
(557, 'dickweasel', 0),
(558, 'dickweed', 0),
(559, 'dickwod', 0),
(560, 'dike', 0),
(561, 'dipshit', 0),
(562, 'doochbag', 0),
(563, 'dookie', 0),
(564, 'douche', 0),
(565, 'douchebag', 0),
(566, 'douche-fag', 0),
(567, 'douchewaffle', 0),
(568, 'dumass', 0),
(569, 'dumb ass', 0),
(570, 'dumbass', 0),
(571, 'dumbfuck', 0),
(572, 'dumbshit', 0),
(573, 'dumshit', 0),
(574, 'fagbag', 0),
(575, 'fagfucker', 0),
(576, 'faggit', 0),
(577, 'faggotcock', 0),
(578, 'fagtard', 0),
(579, 'feltch', 0),
(580, 'flamer', 0),
(581, 'fuckass', 0),
(582, 'fuckbag', 0),
(583, 'fuckboy', 0),
(584, 'fuckbrain', 0),
(585, 'fuckbutt', 0),
(586, 'fuckbutter', 0),
(587, 'fuckersucker', 0),
(588, 'fuckface', 0),
(589, 'fuckhole', 0),
(590, 'fucknut', 0),
(591, 'fucknutt', 0),
(592, 'fuckoff', 0),
(593, 'fuckstick', 0),
(594, 'fucktard', 0),
(595, 'fucktart', 0),
(596, 'fuckup', 0),
(597, 'fuckwad', 0),
(598, 'fuckwitt', 0),
(599, 'gay', 0),
(600, 'gayass', 0),
(601, 'gaybob', 0),
(602, 'gaydo', 0),
(603, 'gayfuck', 0),
(604, 'gayfuckist', 0),
(605, 'gaytard', 0),
(606, 'gaywad', 0),
(607, 'goddamnit', 0),
(608, 'gooch', 0),
(609, 'gook', 0),
(610, 'gringo', 0),
(611, 'guido', 0),
(612, 'handjob', 0),
(613, 'hard on', 0),
(614, 'heeb', 0),
(615, 'ho', 1),
(616, 'hoe', 0),
(617, 'homodumbshit', 0),
(618, 'honkey', 0),
(619, 'humping', 0),
(620, 'jackass', 0),
(621, 'jagoff', 0),
(622, 'jerk off', 0),
(623, 'jerkass', 0),
(624, 'jigaboo', 0),
(625, 'jungle bunny', 0),
(626, 'junglebunny', 0),
(627, 'kike', 0),
(628, 'kooch', 0),
(629, 'kootch', 0),
(630, 'kraut', 0),
(631, 'kunt', 0),
(632, 'kyke', 0),
(633, 'lameass', 0),
(634, 'lardass', 0),
(635, 'lesbian', 0),
(636, 'lesbo', 0),
(637, 'lezzie', 0),
(638, 'mcfagget', 0),
(639, 'mick', 0),
(640, 'minge', 0),
(641, 'muffdiver', 0),
(642, 'munging', 0),
(643, 'negro', 0),
(644, 'nigaboo', 0),
(645, 'niglet', 0),
(646, 'nut sack', 0),
(647, 'paki', 0),
(648, 'panooch', 0),
(649, 'peckerhead', 0),
(650, 'penisbanger', 0),
(651, 'penispuffer', 0),
(652, 'pissed off', 0),
(653, 'polesmoker', 0),
(654, 'pollock', 0),
(655, 'poon', 0),
(656, 'poonani', 0),
(657, 'poonany', 0),
(658, 'poontang', 0),
(659, 'porch monkey', 0),
(660, 'porchmonkey', 0),
(661, 'punanny', 0),
(662, 'punta', 0),
(663, 'pussylicking', 0),
(664, 'puto', 0),
(665, 'queef', 0),
(666, 'queer', 0),
(667, 'queerbait', 0),
(668, 'queerhole', 0),
(669, 'renob', 0),
(670, 'rimjob', 0),
(671, 'ruski', 0),
(672, 'sand nigger', 0),
(673, 'sandnigger', 0),
(674, 'shitass', 0),
(675, 'shitbag', 0),
(676, 'shitbagger', 0),
(677, 'shitbrains', 0),
(678, 'shitbreath', 0),
(679, 'shitcanned', 0),
(680, 'shitcunt', 0),
(681, 'shitface', 0),
(682, 'shitfaced', 0),
(683, 'shithole', 0),
(684, 'shithouse', 0),
(685, 'shitspitter', 0),
(686, 'shitstain', 0),
(687, 'shittiest', 0),
(688, 'shiz', 0),
(689, 'shiznit', 0),
(690, 'skeet', 0),
(691, 'skullfuck', 0),
(692, 'slutbag', 0),
(693, 'smeg', 0),
(694, 'spic', 0),
(695, 'spick', 0),
(696, 'splooge', 0),
(697, 'spook', 0),
(698, 'suckass', 0),
(699, 'tard', 0),
(700, 'thundercunt', 0),
(701, 'twatlips', 0),
(702, 'twats', 0),
(703, 'twatwaffle', 0),
(704, 'unclefucker', 0),
(705, 'vag', 0),
(706, 'vajayjay', 0),
(707, 'va-j-j', 0),
(708, 'vjayjay', 0),
(709, 'wankjob', 0),
(710, 'wetback', 0),
(711, 'whorebag', 0),
(712, 'whoreface', 0),
(713, 'wop', 0),
(714, 'breeder', 0),
(715, 'cocklump', 0),
(716, 'creampie', 0),
(717, 'doublelift', 0),
(718, 'dumbcunt', 0),
(719, 'fuck off', 0),
(720, 'incest', 0),
(721, 'jack Off', 0),
(722, 'poopuncher', 0),
(723, 'sandler', 0),
(724, 'cockeye', 0),
(725, 'crotte', 0),
(726, 'foah', 0),
(727, 'fucktwat', 0),
(728, 'jaggi', 0),
(729, 'kunja', 0),
(730, 'pust', 0),
(731, 'sanger', 0),
(732, 'seks', 0),
(733, 'slag', 0),
(734, 'zubb', 0),
(735, '2g1c', 0),
(736, '2 girls 1 cup', 0),
(737, 'acrotomophilia', 0),
(738, 'alabama hot pocket', 0),
(739, 'alaskan pipeline', 0),
(740, 'anilingus', 0),
(741, 'apeshit', 0),
(742, 'auto erotic', 0),
(743, 'autoerotic', 0),
(744, 'babeland', 0),
(745, 'baby batter', 0),
(746, 'baby juice', 0),
(747, 'ball gag', 0),
(748, 'ball gravy', 0),
(749, 'ball kicking', 0),
(750, 'ball licking', 0),
(751, 'ball sack', 0),
(752, 'ball sucking', 0),
(753, 'bangbros', 0),
(754, 'bareback', 0),
(755, 'barely legal', 0),
(756, 'barenaked', 0),
(757, 'bastardo', 0),
(758, 'bastinado', 0),
(759, 'bbw', 0),
(760, 'bdsm', 0),
(761, 'beaners', 0),
(762, 'beaver cleaver', 0),
(763, 'beaver lips', 0),
(764, 'big black', 0),
(765, 'big breasts', 0),
(766, 'big knockers', 0),
(767, 'big tits', 0),
(768, 'bimbos', 0),
(769, 'birdlock', 0),
(770, 'black cock', 0),
(771, 'blonde action', 0),
(772, 'blonde on blonde action', 0),
(773, 'blow your load', 0),
(774, 'blue waffle', 0),
(775, 'blumpkin', 0),
(776, 'bondage', 0),
(777, 'booty call', 0),
(778, 'brown showers', 0),
(779, 'brunette action', 0),
(780, 'bukkake', 0),
(781, 'bulldyke', 0),
(782, 'bullet vibe', 0),
(783, 'bung hole', 0),
(784, 'bunghole', 0),
(785, 'busty', 0),
(786, 'buttcheeks', 0),
(787, 'camgirl', 0),
(788, 'camslut', 0),
(789, 'camwhore', 0),
(790, 'chocolate rosebuds', 0),
(791, 'circlejerk', 0),
(792, 'cleveland steamer', 0),
(793, 'clover clamps', 0),
(794, 'coprolagnia', 0),
(795, 'coprophilia', 0),
(796, 'cornhole', 0),
(797, 'coons', 0),
(798, 'darkie', 0),
(799, 'date rape', 0),
(800, 'daterape', 0),
(801, 'deep throat', 0),
(802, 'deepthroat', 0),
(803, 'dendrophilia', 0),
(804, 'dingleberry', 0),
(805, 'dingleberries', 0),
(806, 'dirty pillows', 0),
(807, 'dirty sanchez', 0),
(808, 'doggie style', 0),
(809, 'doggiestyle', 0),
(810, 'doggy style', 0),
(811, 'doggystyle', 0),
(812, 'dog style', 0),
(813, 'dolcett', 0),
(814, 'domination', 0),
(815, 'dominatrix', 0),
(816, 'dommes', 0),
(817, 'donkey punch', 0),
(818, 'double dong', 0),
(819, 'double penetration', 0),
(820, 'dp action', 0),
(821, 'dry hump', 0),
(822, 'dvda', 0),
(823, 'eat my ass', 0),
(824, 'ecchi', 0),
(825, 'erotic', 0),
(826, 'erotism', 0),
(827, 'escort', 0),
(828, 'eunuch', 0),
(829, 'fecal', 0),
(830, 'felch', 0),
(831, 'female squirting', 0),
(832, 'femdom', 0),
(833, 'figging', 0),
(834, 'fingerbang', 0),
(835, 'fingering', 0),
(836, 'fisting', 0),
(837, 'foot fetish', 0),
(838, 'footjob', 0),
(839, 'frotting', 0),
(840, 'fuck buttons', 0),
(841, 'fucktards', 0),
(842, 'futanari', 0),
(843, 'gang bang', 0),
(844, 'gay sex', 0),
(845, 'genitals', 0),
(846, 'giant cock', 0),
(847, 'girl on', 0),
(848, 'girl on top', 0),
(849, 'girls gone wild', 0),
(850, 'goatcx', 0),
(851, 'god damn', 0),
(852, 'gokkun', 0),
(853, 'golden shower', 0),
(854, 'goodpoop', 0),
(855, 'goo girl', 0),
(856, 'goregasm', 0),
(857, 'grope', 0),
(858, 'group sex', 0),
(859, 'g-spot', 0),
(860, 'guro', 0),
(861, 'hand job', 0),
(862, 'hard core', 0),
(863, 'hardcore', 0),
(864, 'hentai', 0),
(865, 'homoerotic', 0),
(866, 'hooker', 0),
(867, 'hot carl', 0),
(868, 'hot chick', 0),
(869, 'how to kill', 0),
(870, 'how to murder', 0),
(871, 'huge fat', 0),
(872, 'intercourse', 0),
(873, 'jail bait', 0),
(874, 'jailbait', 0),
(875, 'jelly donut', 0),
(876, 'jiggaboo', 0),
(877, 'jiggerboo', 0),
(878, 'juggs', 0),
(879, 'kinbaku', 0),
(880, 'kinkster', 0),
(881, 'kinky', 0),
(882, 'knobbing', 0),
(883, 'leather restraint', 0),
(884, 'leather straight jacket', 0),
(885, 'lemon party', 0),
(886, 'lolita', 0),
(887, 'lovemaking', 0),
(888, 'make me come', 0),
(889, 'male squirting', 0),
(890, 'menage a trois', 0),
(891, 'milf', 0),
(892, 'missionary position', 0),
(893, 'mound of venus', 0),
(894, 'mr hands', 0),
(895, 'muff diver', 0),
(896, 'muffdiving', 0),
(897, 'nambla', 0),
(898, 'nawashi', 0),
(899, 'neonazi', 0),
(900, 'nig nog', 0),
(901, 'nimphomania', 0),
(902, 'nipple', 0),
(903, 'nipples', 0),
(904, 'nsfw images', 0),
(905, 'nude', 0),
(906, 'nudity', 0),
(907, 'nympho', 0),
(908, 'nymphomania', 0),
(909, 'octopussy', 0),
(910, 'omorashi', 0),
(911, 'one cup two girls', 0),
(912, 'one guy one jar', 0),
(913, 'orgy', 0),
(914, 'paedophile', 0),
(915, 'panties', 0),
(916, 'panty', 0),
(917, 'pedobear', 0),
(918, 'pedophile', 0),
(919, 'pegging', 0),
(920, 'phone sex', 0),
(921, 'piece of shit', 0),
(922, 'piss pig', 0),
(923, 'pisspig', 0),
(924, 'playboy', 0),
(925, 'pleasure chest', 0),
(926, 'pole smoker', 0),
(927, 'ponyplay', 0),
(928, 'poof', 0),
(929, 'punany', 0),
(930, 'poop chute', 0),
(931, 'poopchute', 0),
(932, 'prince albert piercing', 0),
(933, 'pthc', 0),
(934, 'pubes', 0),
(935, 'queaf', 0),
(936, 'quim', 0),
(937, 'raghead', 0),
(938, 'raging boner', 0),
(939, 'rape', 0),
(940, 'raping', 0),
(941, 'rapist', 0),
(942, 'reverse cowgirl', 0),
(943, 'rosy palm', 0),
(944, 'rosy palm and her 5 sisters', 0),
(945, 'rusty trombone', 0),
(946, 'sadism', 0),
(947, 'santorum', 0),
(948, 'scat', 0),
(949, 'scissoring', 0),
(950, 'sexo', 0),
(951, 'sexy', 0),
(952, 'shaved beaver', 0),
(953, 'shaved pussy', 0),
(954, 'shibari', 0),
(955, 'shitblimp', 0),
(956, 'shota', 0),
(957, 'shrimping', 0),
(958, 'slanteye', 0),
(959, 's&m', 0),
(960, 'snowballing', 0),
(961, 'sodomize', 0),
(962, 'sodomy', 0),
(963, 'splooge moose', 0),
(964, 'spooge', 0),
(965, 'spread legs', 0),
(966, 'strap on', 0),
(967, 'strapon', 0),
(968, 'strappado', 0),
(969, 'strip club', 0),
(970, 'style doggy', 0),
(971, 'suck', 0),
(972, 'sucks', 0),
(973, 'suicide girls', 0),
(974, 'sultry women', 0),
(975, 'swastika', 0),
(976, 'swinger', 0),
(977, 'tainted love', 0),
(978, 'taste my', 0),
(979, 'tea bagging', 0),
(980, 'threesome', 0),
(981, 'throating', 0),
(982, 'tied up', 0),
(983, 'tight white', 0),
(984, 'titty', 0),
(985, 'tongue in a', 0),
(986, 'topless', 0),
(987, 'towelhead', 0),
(988, 'tranny', 0),
(989, 'tribadism', 0),
(990, 'tub girl', 0),
(991, 'tubgirl', 0),
(992, 'tushy', 0),
(993, 'twink', 0),
(994, 'twinkie', 0),
(995, 'two girls one cup', 0),
(996, 'undressing', 0),
(997, 'upskirt', 0),
(998, 'urethra play', 0),
(999, 'urophilia', 0),
(1000, 'venus mound', 0),
(1001, 'vibrator', 0),
(1002, 'violet wand', 0),
(1003, 'vorarephilia', 0),
(1004, 'voyeur', 0),
(1005, 'wet dream', 0),
(1006, 'white power', 0),
(1007, 'wrapping men', 0),
(1008, 'wrinkled starfish', 0),
(1009, 'xx', 0),
(1010, 'yaoi', 0),
(1011, 'yellow showers', 0),
(1012, 'yiffy', 0),
(1013, 'zoophilia', 0);

-- --------------------------------------------------------

--
-- Table structure for table `bonus_points`
--

CREATE TABLE `bonus_points` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'Primary Key',
  `bonus_key` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `points` int(10) UNSIGNED DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `bonus_points`
--

INSERT INTO `bonus_points` (`id`, `bonus_key`, `points`) VALUES
(1, 'Lesson Completion', 5),
(2, 'Quiz activity completion (per activity type per card)', 5),
(3, 'Comprehension Completion', 15),
(4, 'Unit Completion', 15),
(5, 'Chapter Test Completion', 50),
(6, 'User social point per Post or Reply', 5);

-- --------------------------------------------------------

--
-- Table structure for table `cards`
--

CREATE TABLE `cards` (
  `id` int(11) UNSIGNED NOT NULL,
  `inflection_id` int(11) UNSIGNED DEFAULT NULL,
  `reference_dictionary_id` int(11) UNSIGNED DEFAULT NULL,
  `image_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'Link to image file in form of an id',
  `video_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'Link to video file in form of an id',
  `audio` varchar(100) DEFAULT NULL COMMENT 'Link to audio file in form of an id',
  `card_type_id` int(11) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Type of card (Word, Verb or Pattern). This changes what type of exercises is possible, etc.',
  `lakota` varchar(255) NOT NULL COMMENT 'Lakota text of the word or pattern',
  `english` varchar(255) NOT NULL COMMENT 'English text of the word or pattern',
  `gender` varchar(255) NOT NULL COMMENT 'Gender of the word, if applicable',
  `include_review` enum('1','0') NOT NULL DEFAULT '1' COMMENT 'Whether or not to include the card in the review session',
  `alt_lakota` varchar(255) DEFAULT NULL COMMENT 'Alternative Lakota text for this word/pattern',
  `alt_english` varchar(255) DEFAULT NULL COMMENT 'Alternative English text for this word/pattern',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `is_active` tinyint(3) NOT NULL DEFAULT '1' COMMENT 'TODO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `cards`
--

INSERT INTO `cards` (`id`, `inflection_id`, `reference_dictionary_id`, `image_id`, `video_id`, `audio`, `card_type_id`, `lakota`, `english`, `gender`, `include_review`, `alt_lakota`, `alt_english`, `created`, `modified`, `is_active`) VALUES
(1, NULL, NULL, 3, 7, '6', 1, 'Lakota <b><color=red>bold red</color></b> and <u><color=blue>underlined blue</color></u> and <i><color=orange>italic orange</color></i>', 'English <b><color=red>bold red</color></b> and <u><color=blue>underlined blue</color></u> and <i><color=orange>italic orange</color></i>', 'default', '1', '', '', '2024-09-15 10:43:50', '2024-09-15 10:43:50', 1),
(2, NULL, NULL, 10, 12, '8', 1, 'Lakota <b><color=green>bold green</color></b> and <u><color=violet>underlined violet</color></u> and <i><color=brown>italic brown</color></i>', 'English <b><color=green>bold green</color></b> and <u><color=violet>underlined violet</color></u> and <i><color=brown>italic brown</color></i>', 'default', '1', '', '', '2024-09-16 12:49:44', '2024-09-16 12:49:44', 1),
(3, NULL, NULL, 14, 15, '9', 1, 'Lakota <b><color=brown>bold brown</color></b> and <u><color=pink>underlined pink</color></u> and <i><color=gray>italic gray</color></i>', 'English <b><color=brown>bold brown</color></b> and <u><color=pink>underlined pink</color></u> and <i><color=gray>italic gray</color></i>', 'default', '1', '', '', '2024-09-16 12:58:45', '2024-09-16 18:09:01', 1),
(4, NULL, NULL, 17, 18, '16', 1, 'Lakota <b><color=#8927d9>bold purple</color></b> and <u><color=#b32469>underlined magenta</color></u> and <i><color=#178a1d>italic green</color></i>', 'English <b><color=#8927d9>bold purple</color></b> and <u><color=#b32469>underlined magenta</color></u> and <i><color=#178a1d>italic green</color></i>', 'default', '1', '', '', '2024-09-18 07:37:44', '2024-09-18 07:58:59', 1),
(5, NULL, NULL, NULL, NULL, NULL, 1, 'Lakota 5', 'English 5', 'default', '1', '', '', '2025-03-14 14:58:38', '2025-03-14 14:58:38', 1),
(6, NULL, NULL, NULL, NULL, NULL, 1, 'Lakota 6', 'English 6', 'default', '1', '', '', '2025-03-14 14:58:45', '2025-03-14 14:58:45', 1),
(7, NULL, NULL, NULL, NULL, NULL, 1, 'Lakota 7', 'English 7', 'default', '1', '', '', '2025-03-14 14:58:54', '2025-03-14 14:58:54', 1),
(8, NULL, NULL, NULL, NULL, NULL, 1, 'Lakota 8', 'English 8', 'default', '1', '', '', '2025-03-14 14:59:00', '2025-03-14 14:59:00', 1),
(9, NULL, NULL, NULL, NULL, NULL, 1, 'Lakota 9', 'English 9', 'default', '1', '', '', '2025-03-14 14:59:08', '2025-03-14 14:59:08', 1),
(10, NULL, NULL, NULL, NULL, NULL, 1, 'Lakota 10', 'English 10', 'default', '1', '', '', '2025-03-14 14:59:15', '2025-03-14 14:59:15', 1),
(11, NULL, NULL, NULL, NULL, NULL, 1, 'Lakota 11', 'English 11', 'default', '1', '', '', '2025-03-14 14:59:23', '2025-03-14 14:59:23', 1);

-- --------------------------------------------------------

--
-- Table structure for table `card_card_groups`
--

CREATE TABLE `card_card_groups` (
  `id` int(11) UNSIGNED NOT NULL,
  `card_id` int(11) UNSIGNED NOT NULL,
  `card_group_id` int(11) UNSIGNED NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `card_card_groups`
--

INSERT INTO `card_card_groups` (`id`, `card_id`, `card_group_id`, `created`, `modified`) VALUES
(1, 1, 1, '2025-05-07 23:00:51', '2025-05-07 23:00:51'),
(2, 2, 1, '2025-05-07 23:00:51', '2025-05-07 23:00:51'),
(3, 3, 1, '2025-05-07 23:00:51', '2025-05-07 23:00:51'),
(4, 4, 1, '2025-05-07 23:00:51', '2025-05-07 23:00:51'),
(5, 5, 1, '2025-05-07 23:00:51', '2025-05-07 23:00:51'),
(6, 10, 2, '2025-05-07 23:01:12', '2025-05-07 23:01:12'),
(7, 11, 2, '2025-05-07 23:01:12', '2025-05-07 23:01:12'),
(8, 5, 2, '2025-05-07 23:01:12', '2025-05-07 23:01:12'),
(9, 6, 2, '2025-05-07 23:01:12', '2025-05-07 23:01:12'),
(10, 7, 2, '2025-05-07 23:01:12', '2025-05-07 23:01:12'),
(11, 8, 2, '2025-05-07 23:01:12', '2025-05-07 23:01:12'),
(12, 9, 2, '2025-05-07 23:01:12', '2025-05-07 23:01:12');

-- --------------------------------------------------------

--
-- Table structure for table `card_groups`
--

CREATE TABLE `card_groups` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `card_group_type_id` int(11) UNSIGNED NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `card_groups`
--

INSERT INTO `card_groups` (`id`, `name`, `card_group_type_id`, `created`, `modified`) VALUES
(1, 'Group 1', 1, '2025-05-07 23:00:51', '2025-05-07 23:00:51'),
(2, 'Group 2', 1, '2025-05-07 23:01:12', '2025-05-07 23:01:12');

-- --------------------------------------------------------

--
-- Table structure for table `card_group_types`
--

CREATE TABLE `card_group_types` (
  `id` int(11) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `card_group_types`
--

INSERT INTO `card_group_types` (`id`, `title`, `created`, `modified`) VALUES
(1, 'listening', NULL, NULL),
(2, 'reading', NULL, NULL),
(3, 'writing', NULL, NULL),
(4, 'speaking', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `card_types`
--

CREATE TABLE `card_types` (
  `id` int(11) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `card_types`
--

INSERT INTO `card_types` (`id`, `title`, `created`, `modified`) VALUES
(1, 'Word', NULL, NULL),
(2, 'Verb', NULL, NULL),
(3, 'Pattern', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `card_units`
--

CREATE TABLE `card_units` (
  `id` int(11) NOT NULL,
  `card_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'ID of the card in question',
  `unit_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Unit to which it belongs'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `card_units`
--

INSERT INTO `card_units` (`id`, `card_id`, `unit_id`) VALUES
(62, 1, 5),
(67, 1, 10),
(73, 1, 15),
(134, 1, 10),
(135, 2, 10),
(140, 1, 1),
(159, 1, 2),
(160, 1, 3),
(165, 1, 4),
(194, 1, 6),
(195, 2, 6),
(196, 1, 7),
(197, 2, 7),
(216, 1, 8),
(217, 2, 8),
(228, 1, 9),
(229, 2, 9),
(237, 1, 11),
(238, 2, 11),
(239, 3, 11),
(252, 1, 12),
(253, 2, 12),
(254, 3, 12),
(267, 1, 13),
(268, 2, 13),
(269, 3, 13),
(277, 1, 14),
(278, 2, 14),
(279, 3, 14),
(280, 1, 15),
(281, 2, 15),
(282, 3, 15),
(284, 1, 16),
(300, 2, 17),
(301, 1, 17),
(302, 3, 17),
(303, 2, 19),
(304, 4, 19),
(305, 1, 19),
(306, 3, 19),
(307, 1, 20),
(308, 2, 20),
(309, 3, 20),
(310, 4, 20),
(312, 1, 23),
(313, 1, 22),
(314, 2, 22),
(315, 3, 22),
(316, 4, 22),
(317, 1, 21),
(318, 2, 21),
(319, 3, 21),
(320, 4, 21),
(321, 1, 28),
(322, 2, 28),
(323, 1, 29),
(327, 1, 33),
(329, 1, 36),
(330, 2, 36),
(331, 3, 36),
(339, 3, 32),
(340, 1, 32),
(341, 1, 30),
(342, 2, 30),
(343, 1, 35),
(344, 1, 37),
(345, 1, 38),
(346, 2, 38),
(347, 3, 38),
(348, 4, 38),
(349, 5, 38),
(350, 1, 3),
(351, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `classrooms`
--

CREATE TABLE `classrooms` (
  `id` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `school_id` int(11) NOT NULL,
  `level_id` int(11) NOT NULL,
  `teacher_message` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `classroom_level_units`
--

CREATE TABLE `classroom_level_units` (
  `id` int(11) NOT NULL,
  `level_units_id` int(11) NOT NULL,
  `classroom_id` int(11) NOT NULL,
  `optional` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `no_repeat` tinyint(1) NOT NULL DEFAULT '0',
  `release_date` date DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `classroom_users`
--

CREATE TABLE `classroom_users` (
  `id` int(11) NOT NULL,
  `classroom_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `contents`
--

CREATE TABLE `contents` (
  `id` int(11) NOT NULL,
  `keyword` mediumtext NOT NULL,
  `title` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `text_mobile` text NOT NULL,
  `img_mobile` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `contents`
--

INSERT INTO `contents` (`id`, `keyword`, `title`, `text`, `text_mobile`, `img_mobile`) VALUES
(1, 'project', 'About Demo eLearning', '<p>Demo eLearning is a platform and database that act as a centralized environment for all the available lessons and exercises. Any new lessons or exercises that need to be tested due to new features or bugs should be added to this database, in order to keep our test set as complete as possible.&nbsp;&nbsp;</p>\n<p>If this database is updated, it should be exported and the version stored in the elearning-core git repo should be updated to reflect the changes.</p>', '', ''),
(7, 'about', 'About Us', '<p>The Language Conservancy (TLC) is a 501c3 non-profit working to revitalize the endangered languages and support a new generation of “Language Warriors” through language materials, teacher trainings, and media/advocacy projects, in partnership with the Native communities.</p>', '', ''),
(9, 'privacy', 'Privacy Policy', '<h1>PRIVACY NOTICE</h1>\n<p>Last updated March 18, 2024</p>\n<div><br></div>\n<p>This privacy notice for The Language Conservancy, Inc. (\"Company,\" \"we,\" \"us,\" or \"our\"), describes how and why we might collect, store, use, and/or share (\"process\") your information when you use our services (\"Services\"), such as when you:</p>\n<ul>\n    <li>Visit our website, or any website of ours that links to this privacy notice.</li>\n    <li>Download and use our mobile application (@APP_NAME@), our Facebook application (@APP_NAME@), or any other application of ours that links to this privacy notice </li>\n    <li>Engage with us in other related ways, including any sales, marketing, or events</li>\n</ul>\n<p><strong>Questions or concerns? </strong>Reading this privacy notice will help you understand your privacy rights and choices. If you do not agree with our policies and practices, please do not use our Services. If you still have any questions or concerns, please contact us at <a href=\"mailto:tech@languageconservancy.org\">tech@languageconservancy.org</a></p>\n<h2>SUMMARY OF KEY POINTS</h2>\n<p><strong><i>This summary provides key points from our privacy notice, but you can find out more details about any of these topics by clicking the link following each key point or by using our table of contents below to find the section you are looking for. You can also click <a href=\"#table-of-contents\">here</a> to go directly to our table of contents.</i></strong></p>\n<p><strong>What personal information do we process?</strong> When you visit, use, or navigate our Services, we may process personal information depending on how you interact with The Language Conservancy, Inc. and the Services, the choices you make, and the products and features you use. Click <a href=\"#what-information-do-we-collect\">here</a> to learn more.</p>\n<p><strong>Do we process any sensitive personal information?</strong> We do not process sensitive personal information.</p>\n<p><strong>Do we receive any information from third parties? </strong> We do not receive any information from third parties.</p>\n<p><strong>How do we process your information?</strong> We process your information to provide, improve, and administer our Services, communicate with you, for security and fraud prevention, and to comply with law. We may also process your information for other purposes with your consent. We process your information only when we have a valid legal reason to do so. Click <a href=\"#how-we-process-your-info\">here</a> to learn more.</p>\n<p><strong>In what situations and with which types of parties do we share personal information?</strong> We may share information in specific situations and with specific categories of third parties. Click <a href=\"#sharing-personal-info\">here</a> to learn more.</p>\n<p><strong>How do we keep your information safe?</strong> We have organizational and technical processes and procedures in place to protect your personal information. However, no electronic transmission over the internet or information storage technology can be guaranteed to be 100% secure, so we cannot promise or guarantee that hackers, cybercriminals, or other unauthorized third parties will not be able to defeat our security and improperly collect, access, steal, or modify your information.</p>\n<p><strong>What are your rights? </strong> Depending on where you are located geographically, the applicable privacy law may mean you have certain rights regarding your personal information. Click <a href=\"#our-privacy-rights\">here</a> to learn more.</p>\n\n<a id=\"table-of-contents\"></a>\n<h1>TABLE OF CONTENTS</h1>\n<ol>\n    <li><a href=\"#what-info-we-collect\">WHAT INFORMATION DO WE COLLECT?</a></li>\n    <li><a href=\"#how-we-process-your-info\">HOW DO WE PROCESS YOUR INFORMATION?</a></li>\n    <li><a href=\"#what-legal-bases\">WHAT LEGAL BASES DO WE RELY ON TO PROCESS YOUR PERSONAL INFORMATION?</a></li>\n    <li><a href=\"#sharing-personal-info\">WHEN AND WITH WHOM DO WE SHARE YOUR PERSONAL INFORMATION?</a></li>\n    <li><a href=\"#do-we-use-cookies\">DO WE USE COOKIES AND OTHER TRACKING TECHNOLOGIES?</a></li>\n    <li><a href=\"#social-logins\">HOW DO WE HANDLE YOUR SOCIAL LOGINS?</a></li>\n    <li><a href=\"#how-long-we-keep-your-info\">HOW LONG DO WE KEEP YOUR INFORMATION?</a></li>\n    <li><a href=\"#how-we-keep-your-info-safe\">HOW DO WE KEEP YOUR INFORMATION SAFE?</a></li>\n    <li><a href=\"#how-we-process-your-info\">HOW DO WE PROCESS YOUR INFORMATION?</a></li>\n    <li><a href=\"#your-privacy-rights\">WHAT ARE YOUR PRIVACY RIGHTS?</a></li>\n    <li><a href=\"#controls-for-do-not-track\">CONTROLS FOR DO-NOT-TRACK FEATURES</a></li>\n    <li><a href=\"#california-residents\">DO CALIFORNIA RESIDENTS HAVE SPECIFIC PRIVACY RIGHTS?</a></li>\n    <li><a href=\"#updates-to-this-notice\">DO WE MAKE UPDATES TO THIS NOTICE?</a></li>\n    <li><a href=\"#contacting-us\">HOW CAN YOU CONTACT US ABOUT THIS NOTICE?</a></li>\n    <li><a href=\"#review-update-delete-your-data\">HOW CAN YOU REVIEW, UPDATE, OR DELETE THE DATA WE COLLECT FROM YOU?</a></li>\n</ol>\n\n<a id=\"what-information-do-we-collect\"></a>\n<h1>1. WHAT INFORMATION DO WE COLLECT?</h1>\n<h2>Personal information you disclose to us</h2>\n<p><i><b>In short:</b></i> We collect personal information that you provide to us.</p>\n<p>We collect personal information that you voluntarily provide to us when you register on the Services, express an interest in obtaining information about us or our products and Services, when you participate in activities on the Services, or otherwise when you contact us.</p>\n<p><b>Personal Information Provided by You.</b> The personal information that we collect depends on the context of your interactions with us and the Services, the choices you make, and the products and features you use. The personal information we collect may include the following:</p>\n<ul>\n    <li>names</li>\n    <li>email addresses</li>\n    <li>usernames</li>\n    <li>user IDs</li>\n    <li>passwords</li>\n    <li>birthdays</li>\n</ul>\n<p><strong>Sensitive Information.</strong> We do not process sensitive information.</p>\n<p>Social Media Login Data. We may provide you with the option to register with us using your existing social media account details, like your Facebook, Twitter, or other social media account. If you choose to register in this way, we will collect the information described in the section called <a href=\"#social-logins\">\"HOW DO WE HANDLE YOUR SOCIAL LOGINS?\"</a> below.</p>\n<p><strong>Application Data.</strong> If you use our application(s), we also may collect the following information if you choose to provide us with access or permission:</p>\n<ul>\n    <li> <i>Mobile Device Data.</i> We automatically collect device information (such as your mobile device ID, model, and manufacturer), operating system, version information and system configuration information, device and application identification numbers, browser type and version, hardware model Internet service provider and/or mobile carrier, and Internet Protocol (IP) address (or proxy server). If you are using our application(s), we may also collect information about the phone network associated with your mobile device, your mobile device\'s operating system or platform, the type of mobile device you use, your mobile device\'s unique device ID, and information about the features of our application(s) you accessed. </li>\n    <li> <i>Push Notifications.</i> We may request to send you push notifications regarding your account or certain features of the application(s). If you wish to opt out from receiving these types of communications, you may turn them off in your device\'s settings. </li>\n</ul>\n<p>This information is primarily needed to maintain the security and operation of our application(s), for troubleshooting, and for our internal analytics and reporting purposes.</p>\n<p>All personal information that you provide to us must be true, complete, and accurate, and you must notify us of any changes to such personal information.</p>\n<h2>Information automatically collected</h2>\n<p><strong>In Short:</strong> Some information - such as your Internet Protocol (IP) address and/or browser and device characteristics is collected automatically when you visit our Services.</p>\n<p>We automatically collect certain information when you visit, use, or navigate the Services. This information does not reveal your specific identity (like your name or contact information) but may include device and usage information, such as your IP address, browser and device characteristics, operating system, language preferences, referring URLs, device name, country, location, information about how and when you use our Services, and other technical information. This information is primarily needed to maintain the security and operation of our Services, and for our internal analytics and reporting purposes.</p>\n<p>Like many businesses, we also collect information through cookies and similar technologies.</p> <br>\n<p>The information we collect includes:</p>\n<ul>\n    <li><i>Log and Usage Data.</i> Log and usage data is service-related, diagnostic, usage, and performance information our servers automatically collect when you access or use our Services and which we record in log files. Depending on how you interact with us, this log data may include your IP address, device information, browser type, and settings and information about your activity in the Services (such as the date/time stamps associated with your usage, pages and files viewed, searches, and other actions you take such as which features you use), device event information (such as system activity, error reports (sometimes called \"crash dumps\"), and hardware settings). </li>\n    <li><i>Device Data.</i> We collect device data such as information about your computer, phone, tablet, or other device you use to access the Services. Depending on the device used, this device data may include information such as your IP address (or proxy server), device and application identification numbers, location, browser type, hardware model, Internet service provider and/or mobile carrier, operating system, and system configuration information. </li>\n</ul>\n<p>Information collected when you use our Facebook application(s). We by default access your Facebook basic account information, including your name, email, gender, birthday, current city, and profile picture URL, as well as other information that you choose to make public. We may also request access to other permissions related to your account, such as friends, check-ins, and likes, and you may choose to grant or deny us access to each individual permission. For more information regarding Facebook permissions, refer to the <a target=\"_blank\" rel=\"nofollow\" href=\"https://developers.facebook.com/docs/facebook-login/permissions\">Facebook Permissions Reference</a> page.</p>\n\n<a id=\"how-we-process-your-info\"></a>\n<h1>2. HOW DO WE PROCESS YOUR INFORMATION?</h1>\n<p><strong>In Short:</strong> We process your information to provide, improve, and administer our Services, communicate with you, for security and fraud prevention, and to comply with law. We may also process your information for other purposes with your consent.</p>\n<p><strong>We process your personal information for a variety of reasons, depending on how you interact with our Services, including:</strong></p>\n<ul>\n    <li><strong>To facilitate account creation and authentication and otherwise manage user accounts.</strong> We may process your information so you can create and log in to your account, as well as keep your account in working order. </li>\n    <li><strong>To deliver and facilitate delivery of services to the user.</strong> We may process your information to provide you with the requested service. </li>\n    <li><strong>To send administrative information to you.</strong> We may process your information to send you details about our products and services, changes to our terms and policies, and other similar information. </li>\n    <li><strong>To save or protect an individual\'s vital interest.</strong> We may process your information when necessary to save or protect an individual\'s vital interest, such as to prevent harm. </li>\n</ul>\n\n<a id=\"what-legal-bases\"></a>\n<h1>3. WHAT LEGAL BASES DO WE RELY ON TO PROCESS YOUR INFORMATION?</h1>\n<p><strong>In Short:</strong> We only process your personal information when we believe it is necessary and we have a valid legal reason (i.e., legal basis) to do so under applicable law, like with your consent, to comply with laws, to provide you with services to enter into or fulfill our contractual obligations, to protect your rights, or to fulfill our legitimate business interests.</p>\n<p><b><i><u>If you are located in the EU or UK, this section applies to you.</u></i></b></p>\n<ul>\n    <li><strong>Consent.</strong> We may process your information if you have given us permission (i.e., consent) to use your personal information for a specific purpose. You can withdraw your consent at any time. </li>\n    <li><strong>Performance of a Contract.</strong> We may process your personal information when we believe it is necessary to fulfill our contractual obligations to you, including providing our Services or at your request prior to entering into a contract with you. </li>\n    <li><strong>Legal Obligations.</strong> We may process your information where we believe it is necessary for compliance with our legal obligations, such as to cooperate with a law enforcement body or regulatory agency, exercise or defend our legal rights, or disclose your information as evidence in litigation in which we are involved. </li>\n    <li><strong>Vital Interests.</strong> We may process your information where we believe it is necessary to protect your vital interests or the vital interests of a third party, such as situations involving potential threats to the safety of any person. </li>\n</ul>\n<p><b><i><u>If you are located in Canada, this section applies to you.</u></i></b></p>\n<p>We may process your information if you have given us specific permission (i.e., express consent) to use your personal information for a specific purpose, or in situations where your permission can be inferred (i.e., implied consent). You can withdraw your consent at any time.</p>\n<p>In some exceptional cases, we may be legally permitted under applicable law to process your information without your consent, including, for example:</p>\n<ul>\n    <li>If collection is clearly in the interests of an individual and consent cannot be obtained in a timely way </li>\n    <li>For investigations and fraud detection and prevention</li>\n    <li>For business transactions provided certain conditions are met</li>\n    <li>If it is contained in a witness statement and the collection is necessary to assess, process, or settle an insurance claim </li>\n    <li>For identifying injured, ill, or deceased persons and communicating with next of kin</li>\n    <li>If we have reasonable grounds to believe an individual has been, is, or may be victim of financial abuse </li>\n    <li>If disclosure is required to comply with a subpoena, warrant, court order, or rules of the court relating to the production of records </li>\n    <li>If it was produced by an individual in the course of their employment, business, or profession and the collection is consistent with the purposes for which the information was produced </li>\n    <li>If the collection is solely for journalistic, artistic, or literary purposes</li>\n    <li>If the information is publicly available and is specified by the regulations</li>\n</ul>\n\n<a id=\"sharing-personal-info\"></a>\n<h1>4. WHEN AND WITH WHOM DO WE SHARE YOUR PERSONAL INFORMATION?</h1>\n<p><strong>In Short:</strong> We may share information in specific situations described in this section and/or with the following categories of third parties.</p>\n<p><strong>Vendors, Consultants, and Other Third-Party Service Providers.</strong> We may share your data with third-party vendors, service providers, contractors, or agents (\"<strong>third parties</strong>\") who perform services for us or on our behalf and require access to such information to do that work. We have contracts in place with our third parties, which are designed to help safeguard your personal information. This means that they cannot do anything with your personal information unless we have instructed them to do it. They will also not share your personal information with any organization apart from us. They also commit to protect the data they hold on our behalf and to retain it for the period we instruct. The categories of third parties we may share personal information with are as follows:</p>\n<ul>\n    <li>Data Analytics Services</li>\n    <li>User Account Registration &amp; Authentication Services</li>\n</ul>\n<p>We also may need to share your personal information in the following situations:</p>\n<ul>\n    <li><strong>Business Transfers.</strong> We may share or transfer your information in connection with, or during negotiations of, any merger, sale of company assets, financing, or acquisition of all or a portion of our business to another company. </li>\n    <li><strong>Affiliates.</strong> We may share your information with our affiliates, in which case we will require those affiliates to honor this privacy notice. Affiliates include our parent company and any subsidiaries, joint venture partners, or other companies that we control or that are under common control with us. </li>\n</ul>\n\n<a id=\"do-we-use-cookies\"></a>\n<h1>5. DO WE USE COOKIES AND OTHER TRACKING TECHNOLOGIES?</h1>\n<p><strong>In Short:</strong> We may use cookies and other tracking technologies to collect and store your information.</p>\n<p>We may use cookies to keep your session authenticated and secure, as well as to store user information for use in the application.</p>\n\n<a id=\"social-logins\"></a>\n<h1>6. HOW DO WE HANDLE YOUR SOCIAL LOGINS?</h1>\n<p><strong>In Short:</strong> If you choose to register or log in to our services using a social media account, we may have access to certain information about you.</p>\n<p>Our Services offer you the ability to register and log in using your third-party social media account details (like your Facebook or Twitter logins). Where you choose to do this, we will receive certain profile information about you from your social media provider. The profile information we receive may vary depending on the social media provider concerned, but will often include your name, email address, friends list, and profile picture, as well as other information you choose to make public on such a social media platform. If you log in using Facebook, we may also request access to other permissions related to your account, such as your friends, check-ins, and likes, and you may choose to grant or deny us access to each individual permission.</p>\n<p>We will use the information we receive only for the purposes that are described in this privacy notice or that are otherwise made clear to you on the relevant Services. Please note that we do not control, and are not responsible for, other uses of your personal information by your third-party social media provider. We recommend that you review their privacy notice to understand how they collect, use, and share your personal information, and how you can set your privacy preferences on their sites and apps.</p>\n\n<a id=\"how-long-we-keep-your-info\"></a>\n<h1>7. HOW LONG DO WE KEEP YOUR INFORMATION?</h1>\n<p><strong>In Short:</strong> We keep your information for as long as necessary to fulfill the purposes outlined in this privacy notice unless otherwise required by law.</p>\n<p> We will only keep your personal information for as long as it is necessary for the purposes set out in this privacy notice, unless a longer retention period is required or permitted by law (such as tax, accounting, or other legal requirements). No purpose in this notice will require us keeping your personal information for longer than the period of time in which users have an account with us.</p>\n<p>When we have no ongoing legitimate business need to process your personal information, we will either delete or anonymize such information, or, if this is not possible (for example, because your personal information has been stored in backup archives), then we will securely store your personal information and isolate it from any further processing until deletion is possible.</p>\n\n<a id=\"how-we-keep-your-info-safe\"></a>\n<h1>8. HOW DO WE KEEP YOUR INFORMATION SAFE?</h1>\n<p><strong>In Short:</strong> We aim to protect your personal information through a system of organizational and technical security measures.</p>\n<p>We have implemented appropriate and reasonable technical and organizational security measures designed to protect the security of any personal information we process. However, despite our safeguards and efforts to secure your information, no electronic transmission over the Internet or information storage technology can be guaranteed to be 100% secure, so we cannot promise or guarantee that hackers, cybercriminals, or other unauthorized third parties will not be able to defeat our security and improperly collect, access, steal, or modify your information. Although we will do our best to protect your personal information, transmission of personal information to and from our Services is at your own risk. You should only access the Services within a secure environment.</p>\n\n<a id=\"your-privacy-rights\"></a>\n<h1>9. WHAT ARE YOUR PRIVACY RIGHTS?</h1>\n<p><strong>In Short:</strong> In some regions, such as the European Economic Area (EEA), United Kingdom (UK), and Canada, you have rights that allow you greater access to and control over your personal information. You may review, change, or terminate your account at any time.</p>\n<p>In some regions (like the EEA, UK, and Canada), you have certain rights under applicable data protection laws. These may include the right (i) to request access and obtain a copy of your personal information, (ii) to request rectification or erasure; (iii) to restrict the processing of your personal information; and (iv) if applicable, to data portability. In certain circumstances, you may also have the right to object to the processing of your personal information. You can make such a request by contacting us by using the contact details provided in the section <a href=\"#contacting-us\">\"HOW CAN YOU CONTACT US ABOUT THIS NOTICE?\"</a> below.</p>\n<p>We will consider and act upon any request in accordance with applicable data protection laws.</p>\n<p>If you are located in the EEA or UK and you believe we are unlawfully processing your personal information, you also have the right to complain to your local data protection supervisory authority. You can find their contact details <a target=\"_blank\" rel=\"nofollow\" href=\"https://ec.europa.eu/justice/data-protection/bodies/authorities/index_en.htm.\">here.</a></p>\n<p>If you are located in Switzerland, the contact details for the data protection authorities are available <a target=\"_blank\" rel=\"nofollow\" href=\"https://www.edoeb.admin.ch/edoeb/en/home.html\">here</a></p>\n<p>Withdrawing your consent: If we are relying on your consent to process your personal information, which may be express and/or implied consent depending on the applicable law, you have the right to withdraw your consent at any time. You can withdraw your consent at any time by contacting us by using the contact details provided in the section <a href=\"#contacting-us\">\"HOW CAN YOU CONTACT US ABOUT THIS NOTICE?\"</a> below. </p>\n<p>However, please note that this will not affect the lawfulness of the processing before its withdrawal, nor when applicable law allows, will it affect the processing of your personal information conducted in reliance on lawful processing grounds other than consent.</p>\n<h2>Account Information</h2>\n<p>If you would at any time like to review or change the information in your account or terminate your account, you can:</p>\n<ul>\n    <li>Contact us using the contact information provided.</li>\n</ul>\n<p>Upon your request to terminate your account, we will deactivate or delete your account and information from our active databases. However, we may retain some information in our files to prevent fraud, troubleshoot problems, assist with any investigations, enforce our legal terms and/or comply with applicable legal requirements.</p>\n<p><strong><u>Cookies and similar technologies:</u></strong> Most Web browsers are set to accept cookies by default. If you prefer, you can usually choose to set your browser to remove cookies and to reject cookies. If you choose to remove cookies or reject cookies, this could affect certain features or services of our Services. To opt out of interest-based advertising by advertisers on our Services visit <a target=\"_blank\" rel=\"nofollow\" href=\"https://www.aboutads.info/choices/\">https://www.aboutads.info/choices/</a></p>\n<p>If you have questions or comments about your privacy rights, you may email us at <a href=\"mailto:tech@languageconservancy.org\">tech@languageconservancy.org</a>.</p>\n\n<a id=\"controls-for-do-not-track\"></a>\n<h1>10. CONTROLS FOR DO-NOT-TRACK FEATURES </h1>\n<p>Most web browsers and some mobile operating systems and mobile applications include a Do-Not-Track (\"DNT\") feature or setting you can activate to signal your privacy preference not to have data about your online browsing activities monitored and collected. At this stage no uniform technology standard for recognizing and implementing DNT signals has been finalized. As such, we do not currently respond to DNT browser signals or any other mechanism that automatically communicates your choice not to be tracked online. If a standard for online tracking is adopted that we must follow in the future, we will inform you about that practice in a revised version of this privacy notice.</p>\n\n<a id=\"california-residents\"></a>\n<h1>11. DO CALIFORNIA RESIDENTS HAVE SPECIFIC PRIVACY RIGHTS? </h1>\n<p><strong>In Short:</strong> Yes, if you are a resident of California, you are granted specific rights regarding access to your personal information.</p>\n<p>California Civil Code Section 1798.83, also known as the \"Shine The Light\" law, permits our users who are California residents to request and obtain from us, once a year and free of charge, information about categories of personal information (if any) we disclosed to third parties for direct marketing purposes and the names and addresses of all third parties with which we shared personal information in the immediately preceding calendar year. If you are a California resident and would like to make such a request, please submit your request in writing to us using the contact information provided below.</p>\n<p> If you are under 18 years of age, reside in California, and have a registered account with Services, you have the right to request removal of unwanted data that you publicly post on the Services. To request removal of such data, please contact us using the contact information provided below and include the email address associated with your account and a statement that you reside in California. We will make sure the data is not publicly displayed on the Services, but please be aware that the data may not be completely or comprehensively removed from all our systems (e.g., backups, etc.).</p>\n<h2>CCPA Privacy Notice</h2>\n<p>The California Code of Regulations defines a \"resident\" as:</p>\n<div>\n    <p>(1) every individual who is in the State of California for other than a temporary or transitory purpose and</p>\n    <p>(2) every individual who is domiciled in the State of California who is outside the State of California for a temporary or transitory purpose</p>\n</div>\n<p>All other individuals are defined as \"non-residents.\"</p>\n<p> If this definition of \"resident\" applies to you, we must adhere to certain rights and obligations regarding your personal information.</p>\n<p><strong>What categories of personal information do we collect?</strong></p>\n<p>We have collected the following categories of personal information in the past twelve (12) months:</p> Category Examples Collected A. Identifiers Contact details, such as real name, alias, postal address, telephone or mobile contact number, unique personal identifier, online identifier, Internet Protocol address, email address, and account name YES B. Personal information categories listed in the California Customer Records statute Name, contact information, education, employment, employment history, and financial information YES C. Protected classification characteristics under California or federal law Gender and date of birth YES D. Commercial information Transaction information, purchase history, financial details, and payment information NO E. Biometric information Fingerprints and voiceprints NO F. Internet or other similar network activity Browsing history, search history, online behavior, interest data, and interactions with our and other websites, applications, systems, and advertisements NO G. Geolocation data Device location NO H. Audio, electronic, visual, thermal, olfactory, or similar information Images and audio, video or call recordings created in connection with our business activities NO I. Professional or employment-related information Business contact details in order to provide you our services at a business level or job title, work history, and professional qualifications if you apply for a job with us NO J. Education Information Student records and directory information NO K. Inferences drawn from other personal information Inferences drawn from any of the collected personal information listed above to create a profile or summary about, for example, an individual’s preferences and characteristics NO <p>We may also collect other personal information outside of these categories instances where you interact with us in person, online, or by phone or mail in the context of:</p>\n<ul>\n    <li>Receiving help through our customer support channels;</li>\n    <li>Participation in customer surveys or contests; and</li>\n    <li>Facilitation in the delivery of our Services and to respond to your inquiries. </li>\n</ul>\n<p><strong>How do we use and share your personal information?</strong></p>\n<p>The Language Conservancy, Inc. collects and shares your personal information through:</p>\n<ul>\n    <li>Social media plugins: Facebook Login , Google Login and Clever Login. We use social media features, such as a \"Like\" button, and widgets, such as a \"Share\" button, in our Services. Such features may process your Internet Protocol (IP) address and track which page you are visiting on our website. We may place a cookie to enable the feature to work correctly. If you are logged in on a certain social media platform and you interact with a widget or button belonging to that social media platform, this information may be recorded to your profile of such social media platform. To avoid this, you should log out from that social media platform before accessing or using the Services. Social media features and widgets may be hosted by a third party or hosted directly on our Services. Your interactions with these features are governed by the privacy notices of the companies that provide them. By clicking on one of these buttons, you agree to the use of this plugin and consequently the transfer of personal information to the corresponding social media service. We have no control over the essence and extent of these transmitted data or their additional processing. </li>\n</ul>\n<p>More information about our data collection and sharing practices can be found in this privacy notice. </p>\n<p>You may contact us by visiting <a target=\"_blank\" rel=\"nofollow\" href=\"https://languageconservancy.org/contact-us\">https://languageconservancy.org/contact-us</a>, or by referring to the contact details at the bottom of this document.</p>\n<p>If you are using an authorized agent to exercise your right to opt out we may deny a request if the authorized agent does not submit proof that they have been validly authorized to act on your behalf.</p>\n<p><strong>Will your information be shared with anyone else? </strong></p>\n<p>We may disclose your personal information with our service providers pursuant to a written contract between us and each service provider. Each service provider is a for-profit entity that processes the information on our behalf.</p>\n<p> We may use your personal information for our own business purposes, such as for undertaking internal research for technological development and demonstration. This is not considered to be \"selling\" of your personal information.</p>\n<p>The Language Conservancy, Inc. has not sold any personal information to third parties for a business or commercial purpose in the preceding twelve (12) months. The Language Conservancy, Inc. has disclosed the following categories of personal information to third parties for a business or commercial purpose in the preceding twelve (12) months:</p>\n<ul>\n    <li>Category A. Identifiers, such as contact details like your real name, alias, postal address, telephone or mobile contact number, unique personal identifier, online identifier, Internet Protocol address, email address, and account name. </li>\n    <li>Category B. Personal information, as defined in the California Customer Records law, such as your name, contact information, education, employment, employment history, and financial information. </li>\n</ul>\n<p>The categories of third parties to whom we disclosed personal information for a business or commercial purpose can be found under \"WHEN AND WITH WHOM DO WE SHARE YOUR PERSONAL INFORMATION?\".</p>\n<p><strong>Your rights with respect to your personal data</strong></p>\n<p><u>Right to request deletion of the data — Request to delete</u></p>\n<p>You can ask for the deletion of your personal information. If you ask us to delete your personal information, we will respect your request and delete your personal information, subject to certain exceptions provided by law, such as (but not limited to) the exercise by another consumer of his or her right to free speech, our compliance requirements resulting from a legal obligation, or any processing that may be required to protect against illegal activities.</p>\n<p>You may also delete your account in the Account Settings page of the application.\n<p><u>Right to be informed — Request to know</u></p>\n<p>Depending on the circumstances, you have a right to know:</p>\n<ul>\n    <li>whether we collect and use your personal information;</li>\n    <li>the categories of personal information that we collect;</li>\n    <li>the purposes for which the collected personal information is used;</li>\n    <li>whether we sell your personal information to third parties;</li>\n    <li>the categories of personal information that we sold or disclosed for a business purpose;</li>\n    <li>the categories of third parties to whom the personal information was sold or disclosed for a business purpose; and </li>\n    <li>the business or commercial purpose for collecting or selling personal information.</li>\n</ul>\n<p>In accordance with applicable law, we are not obligated to provide or delete consumer information that is de-identified in response to a consumer request or to re-identify individual data to verify a consumer request.</p>\n<p><u>Right to Non-Discrimination for the Exercise of a Consumer’s Privacy Rights</u></p>\n<p>We will not discriminate against you if you exercise your privacy rights.</p>\n<p><u>Verification process</u></p>\n<p>Upon receiving your request, we will need to verify your identity to determine you are the same person about whom we have the information in our system. These verification efforts require us to ask you to provide information so that we can match it with information you have previously provided us. For instance, depending on the type of request you submit, we may ask you to provide certain information so that we can match the information you provide with the information we already have on file, or we may contact you through a communication method (e.g., phone or email) that you have previously provided to us. We may also use other verification methods as the circumstances dictate. </p>\n<p>We will only use personal information provided in your request to verify your identity or authority to make the request. To the extent possible, we will avoid requesting additional information from you for the purposes of verification. However, if we cannot verify your identity from the information already maintained by us, we may request that you provide additional information for the purposes of verifying your identity and for security or fraud-prevention purposes. We will delete such additionally provided information as soon as we finish verifying you.</p>\n<p><u>Other privacy rights</u></p>\n<ul>\n    <li>You may object to the processing of your personal information.</li>\n    <li>You may request correction of your personal data if it is incorrect or no longer relevant, or ask to restrict the processing of the information. </li>\n    <li>You can designate an authorized agent to make a request under the CCPA on your behalf. We may deny a request from an authorized agent that does not submit proof that they have been validly authorized to act on your behalf in accordance with the CCPA. </li>\n    <li>You may request to opt out from future selling of your personal information to third parties. Upon receiving an opt-out request, we will act upon the request as soon as feasibly possible, but no later than fifteen (15) days from the date of the request submission. </li>\n</ul>\n<p>To exercise these rights, you can contact us by visiting <a target=\"_blank\" rel=\"nofollow\" href=\"https://hoitewoiperes.com/contact-us\">https://hoitewoiperes.com/contact-us</a>, or by referring to the contact details at the bottom of this document. If you have a complaint about how we handle your data, we would like to hear from you.</p>\n\n<a id=\"updates-to-this-notice\"></a>\n<h1>12. DO WE MAKE UPDATES TO THIS NOTICE?</h1>\n<p><strong>In Short:</strong> Yes, we will update this notice as necessary to stay compliant with relevant laws.</p>\n<p>We may update this privacy notice from time to time. The updated version will be indicated by an updated \"Revised\" date and the updated version will be effective as soon as it is accessible. If we make material changes to this privacy notice, we may notify you either by prominently posting a notice of such changes or by directly sending you a notification. We encourage you to review this privacy notice frequently to be informed of how we are protecting your information.</p>\n\n<a id=\"contacting-us\"></a>\n<h1>13. HOW CAN YOU CONTACT US ABOUT THIS NOTICE?</h1>\n<p>If you have questions or comments about this notice, you may email us at <a href=\"mailto:tech@languageconservancy.org\">tech@languageconservancy.org</a> or by post to:</p>\n<p>The Language Conservancy, Inc.<br> 1720 N Kinser Pike<br> Ste 100<br> Bloomington, IN 47404<br> United States </p>\n\n<a id=\"review-update-delete-your-data\"></a>\n<h1>14. HOW CAN YOU REVIEW, UPDATE, OR DELETE THE DATA WE COLLECT FROM YOU?</h1>\n<p> Based on the applicable laws of your country, you may have the right to request access to the personal information we collect from you, change that information, or delete it. To request to review, update, or delete your personal information, please submit a request by contacting <a href=\"mailto:tech@languageconservancy.org\">tech@languageconservancy.org</a>, or go to your account settings page and click the button next to \'Delete Account\'.</p>', '', ''),
(10, 'terms', 'Terms of Service', '<p>By accessing this Website, accessible from localhost, you are agreeing to be bound by these Website Terms and Conditions of Use and agree that you are responsible for the agreement with any applicable local laws. If you disagree with any of these terms, you are prohibited from accessing this site. The materials contained in this Website are protected by copyright and trademark law.</p>\n\n<h2>2. Use License</h2>\n\n<p>Permission is granted to temporarily download one copy of the materials on The Language Conservancy\'s website for personal, non-commercial transitory viewing only. This is the grant of a license, not a transfer of title, and under this license you may not:</p>\n\n<ul>\n  <li>modify or copy the materials;</li>\n  <li>use the materials for any commercial purpose or for any public display;</li>\n  <li>attempt to reverse engineer any software contained on The Language Conservancy\'s website;</li>\n  <li>remove any copyright or other proprietary notations from the materials; or</li>\n  <li>transfer the materials to another person or \"mirror\" the materials on any other server.</li>\n</ul>\n\n<p>This will lead The Language Conservancy to terminate access upon violations of any of these restrictions. Upon termination, your viewing right will also be terminated and you should destroy any downloaded materials in your possession whether it is printed or electronic format. These Terms of Service has been created with the help of the <a target=\"_blank\" rel=\"nofollow\" href=\"https://www.termsofservicegenerator.net\">Terms Of Service Generator</a>.</p>\n\n<h2>3. Disclaimer</h2>\n\n<p>All the materials on The Language Conservancy\'s website are provided \"as is\". The Language Conservancy makes no warranties, may it be expressed or implied, therefore negates all other warranties. Furthermore, The Language Conservancy does not make any representations concerning the accuracy or reliability of the use of the materials on its Website or otherwise relating to such materials or any sites linked to this Website.</p>\n\n<h2>4. Limitations</h2>\n\n<p>The Language Conservancy or its suppliers will not be hold accountable for any damages that will arise with the use or inability to use the materials on The Language Conservancy\'s website, even if The Language Conservancy or an authorized representative of this Website has been notified, orally or written, of the possibility of such damage. Some jurisdiction does not allow limitations on implied warranties or limitations of liability for incidental damages, these limitations may not apply to you.</p>\n\n<h2>5. Revisions and Errata</h2>\n\n<p>The materials appearing on The Language Conservancy\'s website may include technical, typographical, or photographic errors. The Language Conservancy will not promise that any of the materials in this Website are accurate, complete, or current. The Language Conservancy may change the materials contained on its Website at any time without notice. The Language Conservancy does not make any commitment to update the materials.</p>\n\n<h2>6. Links</h2>\n\n<p>The Language Conservancy has not reviewed all of the sites linked to its Website and is not responsible for the contents of any such linked site. The presence of any link does not imply endorsement by The Language Conservancy of the site. The use of any linked website is at the user\'s own risk.</p>\n\n<h2>7. Site Terms of Use Modifications</h2>\n\n<p>The Language Conservancy may revise these Terms of Use for its Website at any time without prior notice. By using this Website, you are agreeing to be bound by the current version of these Terms and Conditions of Use.</p>\n\n<h2>8. Your Privacy</h2>\n\n<p>Please read our <a target=\"_blank\" rel=\"nofollow\" href=\"http://localhost/info/privacy.php\">Privacy Policy</a>.</p>\n\n<h2>9. Governing Law</h2>\n\n<p>Any claim related to The Language Conservancy\'s website shall be governed by the laws of us without regards to its conflict of law provisions.</p>', '', ''),
(11, 'data-deletion', 'User Data Deletion', '<p>If you would like to delete your eLearning account, please send an email to <a href= \"mailto:support@demo.com\">support@demo.com</a> with the <u><b>same email</b></u> that you registered your eLearning account with.</p>\n\n<p>If you do not send the request from the same email account you registered with, you will be asked a series of follow-up questions which you must answer correctly to verify you are the owner of the account.</p>\n\n<p>An administrator will send you a follow-up email once your account and its associated data has been deleted.</p>', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `emailcontents`
--

CREATE TABLE `emailcontents` (
  `id` int(11) UNSIGNED NOT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  `key` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `content` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `emailcontents`
--

INSERT INTO `emailcontents` (`id`, `display_name`, `key`, `subject`, `content`) VALUES
(1, 'Forgot Password', 'forget_password', 'Forget Password Request', '<tr>\n    <td style=\"text-align:left;width:100%;padding-top:20px;padding-left:40px;padding-right:40px;color:#000000;font-family:\'Open Sans\',Calibri,Arial,sans-serif;font-size:24px;font-weight:bold;line-height:24px\">\n        <span style=\"font-size:18px\">Hi #NAME,</span>\n    </td>\n</tr>\n<tr>\n    <td style=\"text-align:left;width:100%;padding-bottom:0px;padding-left:40px;padding-right:40px;color:#333f48;font-family:\'Open Sans\',Calibri,Arial,sans-serif;font-size:15px;line-height:24px\">\n        <p>\n            <span style=\"font-size:16px\">\n                Please click on the following link to reset your password.\n            </span>\n        </p>\n        <table style=\"width:100%; border-collapse: separate; border-spacing: 0 1em;\">\n            <tbody>\n                <tr>\n                    <td>\n                        <a style=\"padding: 10px 20px; color: white; background-color: #047eb9; text-decoration: none; border-radius: 5px;\" href=\"#LINK\">Reset Password</a>\n                    </td>\n                </tr>\n            </tbody>\n        </table>\n        <p>\n            <span style=\"font-size:16px\">\n                If you did not request a password reset, you can safely ignore this email.\n            </span>\n        </p>\n        <p>\n            <span style=\"font-size:16px\">\n                Best Wishes,<br>#APPLICATIONNAME Team\n            </span>\n        </p>\n    </td>\n</tr>'),
(2, 'Invite Mail', 'invite_mail', 'Invite Request Demo', '<tr>\n  <tr>\n      <td style=\"text-align:left;width:100%;padding-top:20px;padding-left:40px;padding-right:40px;color:#000000;font-family:\'Open Sans\',Calibri,Arial,sans-serif;font-size:24px;font-weight:bold;line-height:24px\">\n          <span style=\"font-size:18px\">Hello User,</span>\n      </td>\n  </tr>\n  <tr>\n      <td style=\"text-align:left;width:100%;padding-bottom:0px;padding-left:40px;padding-right:40px;color:#333f48;font-family:\'Open Sans\',Calibri,Arial,sans-serif;font-size:15px;line-height:24px\">\n          <p>\n              <span style=\"font-size:16px\">\n                  Your Friend #NAME has invited you to join in Demo\n              </span>\n          </p>\n          <p>\n              <span style=\"font-size:16px\">\n                  #MESSAGE\n              </span>\n          </p>\n          <table style=\"width:100%\">\n              <tbody>\n                  <tr>\n                      <td><a href=\"#SITE\">Click Here</a> to join the Site</td>\n                  </tr>\n              </tbody>\n          </table>\n          <p>\n              <span style=\"font-size:16px\">\n                  Best Wishes,<br>Team Demo\n              </span>\n          </p>\n      </td>\n  </tr>\n'),
(3, 'Share Recording Audio', 'share_record_audio', 'Share audio form Demo', '<tr>\n    <td style=\"text-align:left;width:100%;padding-top:20px;padding-left:40px;padding-right:40px;color:#000000;font-family:\'Open Sans\',Calibri,Arial,sans-serif;font-size:24px;font-weight:bold;line-height:24px\">\n        <span style=\"font-size:18px\">Hello Friends,</span>\n    </td>\n</tr>\n<tr>\n    <td style=\"text-align:left;width:100%;padding-bottom:0px;padding-left:40px;padding-right:40px;color:#333f48;font-family:\'Open Sans\',Calibri,Arial,sans-serif;font-size:15px;line-height:24px\">\n        <p>\n            <span style=\"font-size:16px\">\n               I have recorded a audio for recording exercise. Please visit the link to hearing the file.\n            </span>\n        </p>\n        <table style=\"width:100%\">\n            <tbody>\n                <tr>\n                    <td>Link</td>\n                    <td><a href=\"#LINK\">#LINK</a></td>\n                </tr>\n            </tbody>\n        </table>\n        <p>\n            <span style=\"font-size:16px\">\n                Best Wishes,<br>Team Owoksape\n            </span>\n        </p>\n    </td>\n</tr>'),
(4, 'Contact Mail', 'contact_mail', 'Contact Request Demo', '<tr>\n    <td style=\"text-align:left;width:100%;padding-top:20px;padding-left:40px;padding-right:40px;color:#000000;font-family:\'Open Sans\',Calibri,Arial,sans-serif;font-size:24px;font-weight:bold;line-height:24px\">\n        <span style=\"font-size:18px\">Hello Admin,</span>\n    </td>\n</tr>\n<tr>\n    <td style=\"text-align:left;width:100%;padding-bottom:0px;padding-left:40px;padding-right:40px;color:#333f48;font-family:\'Open Sans\',Calibri,Arial,sans-serif;font-size:15px;line-height:24px\">\n        <p>\n            <span style=\"font-size:16px\">\n                    Your user contacted you about an issue\n            </span>\n        </p>\n        <p>\n            <span style=\"font-size:16px\">\n                Name : #NAME\n            </span>\n        </p>\n        <p>\n            <span style=\"font-size:16px\">\n                Email : #EMAIL\n            </span>\n        </p>\n        <p>\n            <span style=\"font-size:16px\">\n                Issue :#ISSUE\n            </span>\n        </p>\n        <p>\n            <span style=\"font-size:16px\">\n                Message :#MESSAGE\n            </span>\n        </p>\n        <p>\n            <span style=\"font-size:16px\">\n                Best Wishes,<br>#APPLICATIONNAME Team\n            </span>\n        </p>\n    </td>\n</tr>\n'),
(5, 'Parent Notification', 'parent_notification', 'Your child has created #AN_A #APPLICATIONNAME account', '<tr>\n        <td style=\"text-align:left;width:100%;padding-top:20px;padding-left:40px;padding-right:40px;color:#000000;font-family:\'Open Sans\',Calibri,Arial,sans-serif;font-size:24px;font-weight:bold;line-height:24px\">\n            <span style=\"font-size:16px\">Hi Parent,</span>\n        </td>\n    </tr>\n    <tr>\n        <td style=\"text-align:left;width:100%;padding-bottom:0px;padding-left:40px;padding-right:40px;color:#333f48;font-family:\'Open Sans\',Calibri,Arial,sans-serif;font-size:15px;line-height:24px\">\n            <p>\n                Your child has created an account on #APPLICATIONNAME with the email address <a href=\"mailto:#CHILD_EMAIL\" target=\"_blank\">#CHILD_EMAIL</a> and username #USERNAME.\n            </p>\n            <p>\n                If this is not your child and you didn\'t create this account, we do not store your email address, and there is nothing further for you to do.\n            </p>\n            <p>\n                If this is your child and you do not wish for your child to have a #APPLICATIONNAME account, you may either:\n                <ol>\n                    <li>Contact us at <a href=\"mailto:#SUPPORT_EMAIL\" target=\"_blank\">#SUPPORT_EMAIL</a> and we will delete the account, or</li>\n                    <li>Ask your child to delete the account by logging in and following the instructions in the account settings.</li>\n                </ol>\n                When the account is deleted, all personal data associated with the account will be deleted.\n            </p>\n            <p>\n                For more details about how we protect child data, please refer to our <a href=\"#SITE_URL/about/privacy\" target=\"_blank\">Privacy Policy</a>.\n            </p>\n            <p>\n                To protect your child\'s privacy and safety, some features of #APPLICATIONNAME have been disabled, as follows:\n                <ul>\n                    <li>They will not be visible on the public leaderboard or have access to it.</li>\n                    <li>They will not have access to the village forum or be able to chat with other users.</li>\n                    <li>Their profile will not be publicly accessible.</li>\n                    <li>They will not be able to find and add friends, or have other users add them as a friend.</li>\n                </ul>\n            </p>\n            <p>\n                If you have any questions or concerns, please contact us at <a href=\"mailto:#SUPPORT_EMAIL\" target=\"_blank\">#SUPPORT_EMAIL</a>.\n            </p>\n            <p>\n                Sincerely,\n                <br>#APPLICATIONNAME Team\n            </p>\n        </td>\n    </tr>');

-- --------------------------------------------------------

--
-- Table structure for table `exercises`
--

CREATE TABLE `exercises` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(500) NOT NULL COMMENT 'Admin-given name to the Exercise. Not used in software',
  `exercise_type` varchar(255) DEFAULT NULL COMMENT 'Type of exercise, written out (multiple-choice, truefalse, etc)',
  `card_type` varchar(255) DEFAULT NULL COMMENT 'Type of card, card, custom, card_group, etc',
  `noofcard` int(11) DEFAULT NULL COMMENT 'Number of cards used, TODO',
  `instruction` varchar(500) DEFAULT NULL COMMENT 'Sentence explaining to user what to do in the exercise',
  `bonus` float(11,2) NOT NULL DEFAULT '0.00' COMMENT 'TODO',
  `promteresponsetype` varchar(255) DEFAULT NULL COMMENT 'Required',
  `promotetype` varchar(255) DEFAULT NULL COMMENT 'Which data to display. Match-pair puts this info in exercise options since there are multiple cards each with their own data settings',
  `responsetype` varchar(255) DEFAULT NULL COMMENT 'Which data to display. Match-pair puts this info in exercise options since there are multiple cards each with their own data settings',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

--
-- Dumping data for table `exercises`
--

INSERT INTO `exercises` (`id`, `name`, `exercise_type`, `card_type`, `noofcard`, `instruction`, `bonus`, `promteresponsetype`, `promotetype`, `responsetype`, `created`, `modified`) VALUES
(1, 'MCQ, Card, Lakota -> Lakota (1)', 'multiple-choice', 'card', NULL, 'Cards, Lakota -> Lakota', 3.00, 'l-l', 'l', 'l', '2024-09-16 17:51:19', '2025-03-13 19:05:27'),
(2, 'MCQ, Card, English -> English (2)', 'multiple-choice', 'card', NULL, 'Card, English -> English', 3.00, 'e-e', 'e', 'e', '2024-09-16 18:03:12', '2024-09-18 07:06:03'),
(3, 'MCQ, Card, Audio -> Video (3)', 'multiple-choice', 'card', NULL, 'Card, Audio -> Video', 3.00, 'a-v', 'a', 'v', '2024-09-17 14:31:25', '2024-09-18 08:59:53'),
(4, 'MCQ, Card, Video -> Audio (4)', 'multiple-choice', 'card', NULL, 'Card, Video -> Audio', 3.00, 'v-a', 'v', 'a', '2024-09-17 14:35:04', '2024-09-18 09:00:29'),
(5, 'MCQ, Card, Lakota -> Image (5)', 'multiple-choice', 'card', NULL, 'Card, Lakota -> Image', 3.00, 'l-i', 'l', 'i', '2024-09-17 14:36:05', '2024-09-18 09:01:04'),
(6, 'MCQ, Card, Image -> Lakota (6)', 'multiple-choice', 'card', NULL, 'Card, Image -> Lakota', 3.00, 'i-l', 'i', 'l', '2024-09-17 14:36:54', '2024-09-18 09:01:40'),
(7, 'MCQ, Card, Lakota (E) -> English (L) (1)', 'multiple-choice', 'card', NULL, 'Card, Lakota (E) -> English (L)', 3.00, 'l-e', 'e, l', 'l, e', '2024-09-17 15:40:08', '2024-09-18 07:15:04'),
(8, 'MCQ, Card, Lakota (A) -> Audio (L) (2)', 'multiple-choice', 'card', NULL, 'Card, Lakota (A) -> Audio (L)', 3.00, 'l-a', 'a, l', 'l, a', '2024-09-17 15:45:08', '2024-09-18 07:15:19'),
(9, 'MCQ, Card, Lakota (V) -> Video (L) (3)', 'multiple-choice', 'card', NULL, 'Card, Lakota (V) -> Video (L)', 3.00, 'l-v', 'v, l', 'l, v', '2024-09-17 15:50:25', '2024-09-18 07:16:31'),
(10, 'MCQ, Card, Lakota (I) -> Image (L) (4)', 'multiple-choice', 'card', NULL, 'Card, Lakota (I) -> Image (L)', 3.00, 'l-i', 'i, l', 'l, i', '2024-09-17 15:57:24', '2024-09-17 16:12:57'),
(11, 'MCQ, Card, English (A) -> Audio (E) (5)', 'multiple-choice', 'card', NULL, 'Card, English (A) -> Audio (E)', 3.00, 'e-a', 'a, e', 'e, a', '2024-09-17 15:59:33', '2024-09-17 16:02:32'),
(12, 'MCQ, Card, English (V) -> Video (E) (6)', 'multiple-choice', 'card', NULL, 'Card, English (V) -> Video (E)', 3.00, 'e-v', 'v, e', 'e, v', '2024-09-17 16:01:25', '2024-09-17 16:02:19'),
(13, 'MCQ, Card, Lakota (E) -> Lakota (E) (7)', 'multiple-choice', 'card', NULL, 'Card, Lakota (E) -> Lakota (E)', 3.00, 'l-l', 'e, l', 'e, l', '2024-09-17 16:04:22', '2024-09-17 16:04:48'),
(14, 'MCQ, Card, Lakota (A) -> Lakota (A) (2b)', 'multiple-choice', 'card', NULL, 'Card, Lakota (A) -> Lakota (A)', 3.00, 'l-l', 'a, l', 'a, l', '2024-09-17 16:05:05', '2024-09-17 16:05:39'),
(15, 'MCQ, Card, Video (L) -> Lakota (V) (3b)', 'multiple-choice', 'card', NULL, 'Card, Video (L) -> Lakota (V)', 3.00, 'v-l', 'l, v', 'v, l', '2024-09-17 16:06:09', '2024-09-17 16:07:24'),
(16, 'MCQ, Card, Lakota (V) -> Video (L) (3c)', 'multiple-choice', 'card', NULL, 'Card, Lakota (V) -> Video (L)', 3.00, 'l-v', 'v, l', 'l, v', '2024-09-17 16:08:23', '2024-09-17 16:08:58'),
(17, 'MCQ, Card, Audio (L) -> Lakota (A) (2c)', 'multiple-choice', 'card', NULL, 'Card, Audio (L) -> Lakota (A)', 3.00, 'a-l', 'l, a', 'a, l', '2024-09-17 16:09:41', '2024-09-17 16:10:11'),
(18, 'MCQ, Card, English (L) -> Lakota (E) (1c)', 'multiple-choice', 'card', NULL, 'Card, English (L) -> Lakota (E)', 3.00, 'e-l', 'l, e', 'e, l', '2024-09-17 16:10:42', '2024-09-17 16:11:10'),
(19, 'MCQ, Card, Image (L) -> Lakota (I) (4b)', 'multiple-choice', 'card', NULL, 'Card, Image (L) -> Lakota (I)', 3.00, 'i-l', 'l, i', 'i, l', '2024-09-17 16:13:14', '2024-09-17 16:13:48'),
(20, 'MCQ, Card, Audio (E) -> English (A) (5b)', 'multiple-choice', 'card', NULL, 'Card, Audio (E) -> English (A)', 3.00, 'a-e', 'e, a', 'a, e', '2024-09-17 16:15:06', '2024-09-17 16:15:41'),
(21, 'MCQ, Card, Video (E) -> English (V) (6b)', 'multiple-choice', 'card', NULL, 'Card, Video (E) -> English (V)', 3.00, 'v-e', 'e, v', 'v, e', '2024-09-17 16:16:06', '2024-09-17 16:16:50'),
(22, 'MCQ, Card, English (I) -> Image (E) (7)', 'multiple-choice', 'card', NULL, 'Card, English (I) -> Image (E)', 2.00, 'e-i', 'i, e', 'e, i', '2024-09-18 07:19:57', '2024-09-18 07:20:31'),
(23, 'MCQ, Card, Image (E) -> English (I) (7b)', 'multiple-choice', 'card', NULL, 'Card, Image (E) -> English (I)', 3.00, 'i-e', 'e, i', 'i, e', '2024-09-18 07:20:51', '2024-09-18 07:21:36'),
(24, 'MCQ, Card, Audio (V) -> Video (A) (8)', 'multiple-choice', 'card', NULL, 'Card, Audio (V) -> Video (A)', 2.00, 'a-v', 'v, a', 'a, v', '2024-09-18 07:22:27', '2024-09-18 07:22:57'),
(25, 'MCQ, Card, Video (A) -> Audio (V) (8b)', 'multiple-choice', 'card', NULL, 'Card,  Video (A) -> Audio (V)', 2.00, 'v-a', 'a, v', 'v, a', '2024-09-18 07:23:16', '2024-09-18 07:23:41'),
(26, 'MCQ, Card, Audio (I) -> Image (A) (9)', 'multiple-choice', 'card', NULL, 'Card,  Audio (I) -> Image (A)', 2.00, 'a-i', 'i, a', 'a, i', '2024-09-18 07:24:02', '2024-09-18 07:24:30'),
(27, 'MCQ, Card, Image (A) -> Audio (I) (9b)', 'multiple-choice', 'card', NULL, 'Card, Image (A) -> Audio (I)', 3.00, 'i-a', 'a, i', 'i, a', '2024-09-18 07:24:50', '2024-09-18 07:25:34'),
(28, 'MCQ, Card, Video (I) -> Image (V) (10)', 'multiple-choice', 'card', NULL, 'Card, Video (I) -> Image (V)', 2.00, 'v-i', 'i, v', 'v, i', '2024-09-18 07:25:53', '2024-09-18 07:26:22'),
(29, 'MCQ, Card, Image (V) -> Video (I) (10b)', 'multiple-choice', 'card', NULL, 'Card, Image (V) -> Video (I)', 2.00, 'i-v', 'v, i', 'i, v', '2024-09-18 07:26:35', '2024-09-18 07:27:09'),
(30, 'MCQ, Card, Lakota (E, A) -> English (A, L) (1a)', 'multiple-choice', 'card', NULL, 'Card, Lakota (E, A) -> English (A, L)', 3.00, 'l-e', 'e, a, l', 'l, a, e', '2024-09-18 08:00:29', '2024-09-18 08:02:10'),
(31, 'MCQ, Card, English (A, L) -> Lakota (E, A) (1b)', 'multiple-choice', 'card', NULL, 'Card, English (A, L) -> Lakota (E, A)', 3.00, 'e-l', 'l, a, e', 'e, a, l', '2024-09-18 08:01:59', '2024-09-18 08:04:02'),
(32, 'MCQ, Card, English (A, L) -> Audio (L, E) (1c)', 'multiple-choice', 'card', NULL, 'Card, English (A, L) -> Audio (L, E)', 3.00, 'e-a', 'l, a, e', 'l, e, a', '2024-09-18 08:05:03', '2024-09-18 08:05:47'),
(33, 'MCQ, Card, Audio (L, E) -> English (A, L) (1d)', 'multiple-choice', 'card', NULL, 'Card, Audio (L, E) -> English (A, L)', 3.00, 'a-e', 'l, e, a', 'l, a, e', '2024-09-18 08:06:07', '2024-09-18 08:16:32'),
(34, 'MCQ, Card, Lakota (E, A) -> Audio (L, E) (1e)', 'multiple-choice', 'card', NULL, 'Card, Lakota (E, A) -> Audio (L, E', 3.00, 'l-a', 'e, a, l', 'l, e, a', '2024-09-18 08:32:24', '2024-09-18 08:33:24'),
(35, 'MCQ, Card, Audio (L, E) -> Lakota (E, A) (1f)', 'multiple-choice', 'card', NULL, 'Card, Audio (L, E) -> Lakota (E, A)', 3.00, 'a-l', 'l, e, a', 'e, a, l', '2024-09-18 08:33:51', '2024-09-18 08:34:26'),
(36, 'MCQ, Card, Lakota (E, A, V, I) -> English (A, V, I, L) (1)', 'multiple-choice', 'card', NULL, 'Card, Lakota (E, A, V, I) -> English (A, V, I, L)', 3.00, 'l-e', 'e, i, a, v, l', 'l, i, a, v, e', '2024-09-18 11:05:03', '2024-09-18 11:05:42'),
(37, 'MCQ, Card, English (L, A, V, I) -> Audio (L, E, V, I) (2)', 'multiple-choice', 'card', NULL, 'Card, English (L, A, V, I) -> Audio (L, E, V, I)', 3.00, 'e-a', 'l, i, a, v, e', 'l, e, i, v, a', '2024-09-18 11:06:27', '2024-09-18 11:07:11'),
(38, 'MCQ, Card, Audio (L, E, V, I) -> Video (L, E, A, I) (3)', 'multiple-choice', 'card', NULL, 'Card, Audio (L, E, V, I) -> Video (L, E, A, I)', 2.00, 'a-v', 'l, e, i, v, a', 'l, e, i, a, v', '2024-09-18 11:07:46', '2024-09-18 11:08:25'),
(39, 'MCQ, Card, Video (L, E, A, I) -> Image (L, E, A, V) (4)', 'multiple-choice', 'card', NULL, 'Card, Video (L, E, A, I) -> Image (L, E, A, V)', 3.00, 'v-i', 'l, e, i, a, v', 'l, e, a, v, i', '2024-09-18 11:08:45', '2024-09-18 11:09:20'),
(40, 'MCQ, Card, Image (L, E, A, V) -> Lakota (E, A, V, I) (5)', 'multiple-choice', 'card', NULL, 'Card, Image (L, E, A, V) -> Lakota (E, A, V, I)', 2.00, 'i-l', 'l, e, a, v, i', 'e, i, a, v, l', '2024-09-18 11:09:43', '2024-10-11 19:20:53'),
(42, 'MTP, Card, Lakota (E, A, V, I) -> English (A, V, I, L) (1)', 'match-the-pair', 'card', 4, 'Lakota (E, A, V, I) -> English (A, V, I, L)', 3.00, 'l-e', NULL, NULL, '2024-09-27 18:38:10', '2024-10-14 23:26:04'),
(43, 'MTP, Card, Lakota -> Lakota (1)', 'match-the-pair', 'card', 4, 'Card, Lakota -> Lakota', 3.00, 'l-l', NULL, NULL, '2024-09-27 19:05:47', '2024-09-27 19:07:09'),
(44, 'MTP, Card, English -> English (2)', 'match-the-pair', 'card', 4, 'Card, English -> English', 3.00, 'e-e', NULL, NULL, '2024-09-27 19:06:43', '2024-09-27 19:07:50'),
(45, 'MTP, Card, Audio -> Video (3)', 'match-the-pair', 'card', 4, 'Card, Audio -> Video', 3.00, 'a-v', NULL, NULL, '2024-09-27 19:08:12', '2024-09-27 19:08:51'),
(46, 'MTP, Card, Video -> Audio (4)', 'match-the-pair', 'card', 4, 'Card, Video -> Audio', 3.00, 'v-a', NULL, NULL, '2024-09-27 19:09:16', '2024-09-27 19:10:18'),
(47, 'MTP, Card, Lakota -> Image (5)', 'match-the-pair', 'card', 4, 'Card, Lakota -> Image', 3.00, 'l-i', NULL, NULL, '2024-09-27 19:10:32', '2024-09-27 19:11:06'),
(48, 'MTP, Card, Image -> Lakota (6)', 'match-the-pair', 'card', 4, 'MTP, Card, Image -> Lakota (6)', 4.00, 'i-l', NULL, NULL, '2024-09-27 19:11:24', '2024-11-06 03:10:47'),
(49, 'Anagram, Card, Lakota (E, A) -> English (L, A)', 'anagram', 'card', NULL, 'Anagram, Card, Lakota (E, A) -> English (L, A)', 3.00, 'l-e', 'e, a, l', 'l, a, e', '2024-10-29 16:47:04', '2024-11-02 16:45:38'),
(50, 'Anagram, Card, Lakota -> English', 'anagram', 'card', NULL, 'Anagram, Card, Lakota -> English', 3.00, 'l-e', 'l', 'l, e', '2024-10-29 16:56:53', '2024-11-02 16:45:25'),
(51, 'Anagram, Card, English', 'anagram', 'card', NULL, 'Anagram, Card, English', 3.00, 'a-l', 'a', 'l', '2024-10-29 16:58:09', '2024-11-02 16:45:18'),
(52, 'Anagram, Card, Video', 'anagram', 'card', NULL, 'Anagram, Card, Video', 3.00, 'v-e', 'v', 'l, a, e', '2024-10-29 17:00:07', '2024-11-02 16:45:11'),
(53, 'Anagram, Card, Lakota (E, V) -> English (L, V)', 'anagram', 'card', NULL, 'Anagram, Card, Lakota (E, V) -> English (L, V)', 3.00, 'e-l', 'l, v, e', 'l', '2024-11-02 15:18:21', '2024-11-02 16:45:03'),
(54, 'Anagram, Card, English (L, I) -> Lakota', 'anagram', 'card', NULL, 'Anagram, Card, English (L, I) -> Lakota', 3.00, 'e-l', 'l, i, e', 'l', '2024-11-02 15:19:54', '2024-11-02 16:44:55'),
(55, 'Anagram, Card, Audio (L, V) -> Lakota', 'anagram', 'card', NULL, 'Anagram, Card, Audio (L, V) -> Lakota', 3.00, 'a-l', 'l, v, a', 'l', '2024-11-02 15:20:41', '2024-11-02 16:44:49'),
(56, 'Anagram, Card, Image (L, A) -> Lakota', 'anagram', 'card', NULL, 'Anagram, Card, Image (L, A) -> Lakota', 3.00, 'i-l', 'l, a, i', 'l', '2024-11-02 15:21:16', '2024-11-02 16:44:38'),
(57, 'Anagram, Card, Video (L, I) -> Lakota', 'anagram', 'card', NULL, 'Anagram, Card, Video (L, I) -> Lakota', 3.00, 'v-l', 'l, i, v', 'l', '2024-11-02 15:22:09', '2024-11-02 16:44:22'),
(58, 'Anagram, Card, English (A, V) -> Lakota', 'anagram', 'card', NULL, 'Anagram, Card, English (A, V) -> Lakota', 3.00, 'e-l', 'a, v, e', 'l', '2024-11-02 15:22:48', '2024-11-02 16:44:12'),
(59, 'Anagram, Card, Image (E, A) -> Lakota', 'anagram', 'card', NULL, 'Anagram, Card, Image (E, A) -> Lakota', 2.00, 'i-l', 'e, a, i', 'l', '2024-11-02 15:23:56', '2024-11-02 16:44:01'),
(60, 'Anagram, Card, Video (E, I) -> Lakota', 'anagram', 'card', NULL, 'Anagram, Card, Video (E, I) -> Lakota', 3.00, 'v-l', 'e, i, v', 'l', '2024-11-02 15:25:14', '2024-11-02 16:43:51'),
(61, 'Anagram, Card, Audio (V, I) -> Lakota', 'anagram', 'card', NULL, 'Anagram, Card, Audio (V, I) -> Lakota', 3.00, 'a-l', 'i, v, a', 'l', '2024-11-02 15:25:58', '2024-11-02 16:43:41'),
(62, 'TF, Card, Lakota -> English', 'truefalse', 'card', NULL, 'TF, Card, Lakota -> English', 2.00, 'l-e', 'l', 'e', '2024-11-02 15:37:54', '2024-11-06 03:08:25'),
(63, 'TF, Card, English -> Audio', 'truefalse', 'card', NULL, 'TF, Card, English -> Audio', 2.00, 'e-a', 'e', 'a', '2024-11-02 15:38:38', '2024-11-06 03:08:19'),
(64, 'TF, Card, Audio -> Video', 'truefalse', 'card', NULL, 'TF, Card, Audio -> Video', 3.00, 'a-v', 'a', 'v', '2024-11-02 15:39:17', '2024-11-06 03:08:10'),
(65, 'TF, Card, Video -> Image', 'truefalse', 'card', NULL, 'TF, Card, Video -> Image', 3.00, 'v-i', 'v', 'i', '2024-11-02 15:39:49', '2024-11-06 03:08:03'),
(66, 'TF, Card, Image -> Lakota', 'truefalse', 'card', NULL, 'TF, Card, Image -> Lakota', 3.00, 'i-l', 'i', 'l', '2024-11-02 15:40:24', '2024-11-06 03:07:51'),
(67, 'TF, Card, Lakota (E) -> Audio (V)', 'truefalse', 'card', NULL, 'TF, Card, Lakota (E) -> Audio (V)', 3.00, 'l-a', 'e, l', 'v, a', '2024-11-02 15:42:18', '2024-11-06 03:07:39'),
(68, 'TF, Card, English (A) -> Video (I)', 'truefalse', 'card', NULL, 'TF, Card, English (A) -> Video (I)', 3.00, 'e-v', 'a, e', 'i, v', '2024-11-02 15:42:53', '2024-11-06 03:07:27'),
(69, 'TF, Card, Audio (V) ->  Image (L)', 'truefalse', 'card', NULL, 'TF, Card, Audio (V) ->  Image (L)', 3.00, 'a-i', 'v, a', 'l, i', '2024-11-02 15:44:20', '2024-11-06 03:07:07'),
(70, 'TF, Card, Image (L) ->  Lakota (E)', 'truefalse', 'card', NULL, 'TF, Card, Image (L) ->  Lakota (E)', 4.00, 'i-l', 'l, i', 'e, l', '2024-11-02 15:44:46', '2024-11-06 03:06:50'),
(71, 'TF, Card, Laktoa (I) ->  Lakota (V)', 'truefalse', 'card', NULL, 'TF, Card, Laktoa (I) ->  Lakota (V)', 3.00, 'l-l', 'i, l', 'v, l', '2024-11-02 15:45:36', '2024-11-06 03:06:29'),
(72, 'TF, Card, Lakota (E, A) ->  English (A, V)', 'truefalse', 'card', NULL, 'TF, Card, Lakota (E, A) ->  English (A, V)', 3.00, 'l-e', 'e, a, l', 'a, v, e', '2024-11-02 15:46:19', '2024-11-06 03:45:26'),
(73, 'TF, Card, English (A, V) -> Audio (V, I)', 'truefalse', 'card', NULL, 'TF, Card, English (A, V) -> Audio (V, I)', 3.00, 'e-a', 'a, v, e', 'i, v, a', '2024-11-02 15:47:10', '2024-11-06 03:05:41'),
(74, 'TF, Card, Audio (V, I) ->  Video (I, L)', 'truefalse', 'card', NULL, 'TF, Card, Audio (V, I) ->  Video (I, L)', 3.00, 'a-v', 'i, v, a', 'l, i, v', '2024-11-02 16:10:54', '2024-11-06 03:04:47'),
(75, 'TF, Card, Video (I, L) ->  Image (L, E)', 'truefalse', 'card', NULL, 'TF, Card, Video (I, L) ->  Image (L, E)', 3.00, 'v-i', 'l, i, v', 'l, e, i', '2024-11-02 16:11:33', '2024-11-06 03:01:22'),
(76, 'TF, Card, Image (L, E) ->  Lakota (E, A)', 'truefalse', 'card', NULL, 'TF, Card, Image (L, E) ->  Lakota (E, A)', 3.00, 'i-l', 'l, e, i', 'e, a, l', '2024-11-02 16:12:24', '2024-11-06 03:00:42'),
(77, 'TF, Custom, HTML -> HTML', 'truefalse', 'custom', NULL, 'TF, Custom, HTML -> HTML', 3.00, 'l-e', 'l', 'e', '2024-11-03 21:53:25', '2024-11-04 16:18:15'),
(78, 'TF, Custom, HTML (A,I) -> HTML (A,I)', 'truefalse', 'custom', NULL, 'TF, Custom, HTML (A,I) -> HTML (A,I)', 3.00, 'l-e', 'l, i, a', 'e, i, a', '2024-11-03 21:54:56', '2024-11-06 02:41:40'),
(79, 'TF, Card Group, Audio (V, I) -> Image (L, E)', 'truefalse', 'card_group', 2, 'TF, Card Group, Audio (V, I) -> Image (L, E)', 3.00, 'a-i', 'l, e, i, v, a', 'l, e, a, v, i', '2024-11-03 22:15:16', '2024-11-03 22:30:30'),
(81, 'Fill-in Typing, Card, Lakota (E, A, V, I) ->  Lakota', 'fill_in_the_blanks', 'card', NULL, 'Fill-in Typing, Card, Lakota (E, A, V, I) ->  Lakota', 4.00, 'e-l', 'l, i, a, v, e', 'l', '2024-11-06 03:54:18', '2024-11-06 03:56:31'),
(82, 'Fill-in MCQ, Card, Lakota (E, A, V, I) ->  Lakota', 'fill_in_the_blanks', 'card', NULL, 'Fill-in MCQ, Card, Lakota (E, A, V, I) ->  Lakota', 3.00, 'l-l', 'e, i, a, v, l', 'l', '2024-11-07 18:57:45', '2024-11-07 18:59:43'),
(85, 'Fill-in Typing, Custom/Card, Lakota (E, A, V, I) -> Lakota', 'fill_in_the_blanks', 'custom', NULL, 'Fill-in Typing, Custom/Card, Lakota (E, A, V, I) -> Lakota', 3.00, 'l-l', 'e, i, a, v, l', 'l', '2024-11-07 19:03:28', '2025-03-21 22:41:54'),
(86, 'Fill-in Typing, Custom/HTML, Lakota HTML (A, I) -> Lakota', 'fill_in_the_blanks', 'custom', NULL, 'Fill-in Typing, Custom/HTML, Lakota HTML (A, I) -> Lakota', 3.00, 'l-l', 'l', 'l', '2024-11-07 19:49:23', '2024-11-07 19:50:09'),
(87, 'Anagram, Custom/Card, L (E, A, I, V) -> L', 'anagram', 'card', NULL, 'Anagram, Custom/Card, L (E, A, I, V) -> L', 3.00, 'a-l', 'l, e, i, v, a', 'l', '2024-11-08 19:14:16', '2024-11-08 19:21:05'),
(88, 'Anagram, Custom/Card Group, I (E, A, L, V) -> L', 'anagram', 'card_group', 3, 'Anagram, Custom/Card Group, I (E, A, L, V) -> L', 3.00, 'i-l', 'l, e, a, v, i', 'l', '2024-11-08 19:22:12', '2024-11-08 19:22:55'),
(89, 'MCQ, Custom, HTML (A, I) -> HTML (A, I)', 'multiple-choice', 'custom', NULL, 'MCQ, Custom, HTML (A, I) -> HTML (A, I)', 4.00, 'l-l', 'l', 'l', '2024-11-08 19:38:48', '2024-11-08 19:46:48'),
(90, 'MCQ, Custom, Card Lakota (L, A, I) -> Card Lakota (L, E, A, I, V)', 'multiple-choice', 'custom', NULL, 'MCQ, Custom, Card Lakota (L, A, I) -> Card Lakota (L, E, A, I, V)', 3.00, 'l-l', 'l', 'l', '2024-11-09 00:35:58', '2025-05-03 19:53:23'),
(91, 'MTP, Custom, HTML (A, I) -> HTML (A, I)', 'match-the-pair', 'custom', 4, 'MTP, Custom, HTML (A, I) -> HTML (A, I)', 3.00, 'a-l', NULL, NULL, '2024-11-09 00:42:20', '2024-11-09 00:50:20'),
(92, 'MTP, Custom, Card L (E, A, I, V) -> Card Lakota (E, A, I, V)', 'match-the-pair', 'custom', 3, 'MTP, Custom, Card L (E, A, I, V) -> Card Lakota (E, A, I, V)', 3.00, 'l-l', NULL, NULL, '2024-11-09 00:54:49', '2024-11-09 01:01:18'),
(93, 'MCQ, Custom, HTML -> HTML', 'multiple-choice', 'custom', NULL, 'MCQ, Custom, HTML -> HTML', 3.00, 'e-l', 'e', 'l', '2024-12-11 17:10:26', '2024-12-11 17:12:24'),
(94, 'MCQ, Custom, Card, Audio (I) -> Image (A)', 'multiple-choice', 'custom', NULL, '', 0.00, 'a-i', 'a', 'i', '2025-03-13 20:52:10', '2025-03-14 14:59:42'),
(95, 'Fill-in Typing, Custom, Multiple Same Blanks', 'fill_in_the_blanks', 'card', NULL, 'Fill in the blanks with the correct words', 4.00, 'i-l', 'i', 'l', '2025-03-14 15:22:25', '2025-03-15 19:10:31'),
(96, 'Fill-in MCQ, Card, Multiple Same Blanks', 'fill_in_the_blanks', 'card', NULL, 'Fill-in MCQ, Card, Multiple Same Blanks', 4.00, 'i-l', 'l, i', 'l', '2025-03-15 19:10:42', '2025-05-04 23:00:51'),
(97, 'delete me', 'fill_in_the_blanks', 'custom', NULL, '', 0.00, 'e-i', 'e', 'i', '2025-03-21 22:44:45', '2025-03-21 22:45:19'),
(98, 'MCQ, Custom, HTML -> Card (L, A)', 'multiple-choice', 'custom', NULL, 'Choose the best answer to the question.', 4.00, 'e-l', 'e', 'l', '2025-04-30 18:33:25', '2025-04-30 18:34:46'),
(99, 'deleteme', 'multiple-choice', 'custom', NULL, '', 0.00, 'a-e', 'a', 'e', '2025-04-30 18:45:06', '2025-04-30 18:48:11'),
(100, 'a', NULL, NULL, NULL, NULL, 0.00, NULL, NULL, NULL, '2025-05-01 20:57:38', '2025-05-01 20:57:38'),
(101, 'TF, Custom, Card (A,E) -> Card (A,I)', 'truefalse', 'custom', NULL, 'TF, Custom, Card (A,E) -> Card (A,I)', 3.00, 'a-i', 'a', 'i', '2025-05-01 21:03:58', '2025-05-01 21:48:52'),
(102, 'MCQ, Card Group, Audio (L) -> Image', 'multiple-choice', 'card_group', 1, 'Choose the picture that matches the Crow word.', 3.00, 'a-i', 'l, a', 'e, i', '2025-05-07 22:59:54', '2025-05-07 23:06:20'),
(103, 'MTP, Card Group, Audio (L) -> Image (E)', 'match-the-pair', 'card_group', 4, 'Which audio matches the image?', 3.00, 'a-i', 'l, a', 'e, i', '2025-05-07 23:15:28', '2025-05-07 23:16:10'),
(104, 'MCQ, Card, Select Cards, Lakota (A, E, I) -> Image (L, E, A)', 'multiple-choice', 'card', NULL, 'MCQ, Card, Select Cards, Lakota (A, E, I) -> Image (L, E, A)', 3.00, 'l-i', 'e, i, a, l', 'l, e, a, i', '2025-05-14 17:52:06', '2025-05-14 17:53:08');

-- --------------------------------------------------------

--
-- Table structure for table `exercise_custom_options`
--

CREATE TABLE `exercise_custom_options` (
  `id` int(11) UNSIGNED NOT NULL,
  `exercise_id` int(11) UNSIGNED DEFAULT NULL,
  `exercise_option_id` int(11) UNSIGNED DEFAULT NULL,
  `prompt_audio_id` int(11) UNSIGNED DEFAULT NULL,
  `prompt_image_id` int(11) UNSIGNED DEFAULT NULL,
  `prompt_html` text CHARACTER SET utf8,
  `response_audio_id` int(11) UNSIGNED DEFAULT NULL,
  `response_image_id` int(11) UNSIGNED DEFAULT NULL,
  `response_html` text CHARACTER SET utf8,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `exercise_custom_options`
--

INSERT INTO `exercise_custom_options` (`id`, `exercise_id`, `exercise_option_id`, `prompt_audio_id`, `prompt_image_id`, `prompt_html`, `response_audio_id`, `response_image_id`, `response_html`, `created`, `modified`) VALUES
(8, 77, 550, NULL, NULL, 'Lakota <b>custom</b> <color=red>html</color>', NULL, NULL, 'English <u>custom</u> <color=#009999><i>html</i></color>', '2024-11-04 16:18:15', '2024-11-04 16:18:15'),
(15, 78, 557, 16, 3, 'Lakota html image audio', 11, 17, 'English html image audio', '2024-11-06 02:41:40', '2024-11-06 02:41:40'),
(17, 86, 610, 11, 10, 'This dog is really old', NULL, NULL, NULL, '2024-11-07 19:50:09', '2024-11-07 19:50:09'),
(19, 89, 623, 9, 3, 'This is a custom html multiple choice exercise', 9, 3, 'This is a custom html multiple choice exercise', '2024-11-08 19:46:48', '2024-11-08 19:46:48'),
(20, 89, 624, NULL, NULL, NULL, 16, 10, 'This is wrong 1', '2024-11-08 19:46:48', '2024-11-08 19:46:48'),
(21, 89, 625, NULL, NULL, NULL, 11, 14, 'This is wrong 2', '2024-11-08 19:46:48', '2024-11-08 19:46:48'),
(22, 89, 626, NULL, NULL, NULL, 8, 13, 'This is wrong 3', '2024-11-08 19:46:48', '2024-11-08 19:46:48'),
(23, 91, 632, 6, 3, 'Card 1a is <b>bold</b>', 6, 3, 'Card 1b is <b>bold</b>', '2024-11-09 00:50:20', '2024-11-09 00:50:20'),
(24, 91, 633, 9, 10, 'Card 2a is <u>underlined</u>', 9, 10, 'Card 2b is <u>underlined</u>', '2024-11-09 00:50:20', '2024-11-09 00:50:20'),
(25, 91, 634, 8, 13, 'Card 3a is <color=red>red</color>', 8, 13, 'Card 3b is <color=red>red</color>', '2024-11-09 00:50:20', '2024-11-09 00:50:20'),
(26, 91, 635, 8, 14, 'Card 4a is <i>italic</i>', 16, 14, 'Card 4b is <i>italic</i>', '2024-11-09 00:50:20', '2024-11-09 00:50:20'),
(31, 93, 647, NULL, NULL, 'This is a <b>bold html</b> prompt', NULL, NULL, 'This is a <b>bold html</b> response', '2024-12-11 17:12:24', '2024-12-11 17:12:24'),
(32, 93, 648, NULL, NULL, NULL, NULL, NULL, 'This is <color=red>wrong</color> 1', '2024-12-11 17:12:24', '2024-12-11 17:12:24'),
(33, 93, 649, NULL, NULL, NULL, NULL, NULL, 'This is <color=blue>wrong</color> 2', '2024-12-11 17:12:24', '2024-12-11 17:12:24'),
(34, 93, 650, NULL, NULL, NULL, NULL, NULL, 'This is <color=yellow>wrong</color> 3', '2024-12-11 17:12:24', '2024-12-11 17:12:24'),
(35, 98, 727, NULL, NULL, '<size=24><b>This is worn on the head and is made from porcupine hair and rooster feathers.</b></size>', NULL, NULL, NULL, '2025-04-30 18:34:46', '2025-04-30 18:34:46'),
(39, 101, 739, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-01 21:48:52', '2025-05-01 21:48:52');

-- --------------------------------------------------------

--
-- Table structure for table `exercise_options`
--

CREATE TABLE `exercise_options` (
  `id` int(11) UNSIGNED NOT NULL,
  `type` enum('card','group') DEFAULT NULL,
  `card_type` enum('P','R','O') DEFAULT NULL COMMENT 'P=Promote,R=Response,O=Option',
  `exercise_id` int(11) UNSIGNED NOT NULL,
  `card_id` int(11) UNSIGNED DEFAULT NULL,
  `group_id` int(11) UNSIGNED DEFAULT NULL,
  `responce_card_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'this coloum is for match the pair question',
  `prompt_preview_option` varchar(50) DEFAULT NULL COMMENT 'this coloum is for match the pair question',
  `responce_preview_option` varchar(50) DEFAULT NULL,
  `response_true_false` enum('Y','N') DEFAULT NULL,
  `fill_in_the_blank_type` enum('mcq','typing') DEFAULT NULL,
  `text_option` varchar(255) DEFAULT NULL,
  `option_position` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

--
-- Dumping data for table `exercise_options`
--

INSERT INTO `exercise_options` (`id`, `type`, `card_type`, `exercise_id`, `card_id`, `group_id`, `responce_card_id`, `prompt_preview_option`, `responce_preview_option`, `response_true_false`, `fill_in_the_blank_type`, `text_option`, `option_position`) VALUES
(94, 'card', 'P', 12, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(95, 'card', 'R', 12, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(96, 'card', 'O', 12, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(97, 'card', 'O', 12, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(98, 'group', 'O', 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(99, 'card', 'P', 11, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(100, 'card', 'R', 11, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(101, 'card', 'O', 11, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(102, 'card', 'O', 11, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(103, 'group', 'O', 11, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(124, 'card', 'P', 13, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(125, 'card', 'R', 13, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(126, 'card', 'O', 13, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(127, 'card', 'O', 13, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(128, 'group', 'O', 13, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(129, 'card', 'P', 14, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(130, 'card', 'R', 14, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(131, 'card', 'O', 14, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(132, 'card', 'O', 14, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(133, 'group', 'O', 14, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(134, 'card', 'P', 15, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(135, 'card', 'R', 15, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(136, 'card', 'O', 15, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(137, 'card', 'O', 15, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(138, 'group', 'O', 15, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(139, 'card', 'P', 16, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(140, 'card', 'R', 16, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(141, 'card', 'O', 16, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(142, 'card', 'O', 16, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(143, 'group', 'O', 16, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(144, 'card', 'P', 17, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(145, 'card', 'R', 17, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(146, 'card', 'O', 17, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(147, 'card', 'O', 17, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(148, 'group', 'O', 17, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(149, 'card', 'P', 18, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(150, 'card', 'R', 18, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(151, 'card', 'O', 18, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(152, 'card', 'O', 18, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(153, 'group', 'O', 18, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(154, 'card', 'P', 10, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(155, 'card', 'R', 10, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(156, 'card', 'O', 10, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(157, 'card', 'O', 10, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(158, 'group', 'O', 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(159, 'card', 'P', 19, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(160, 'card', 'R', 19, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(161, 'card', 'O', 19, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(162, 'card', 'O', 19, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(163, 'group', 'O', 19, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(164, 'card', 'P', 20, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(165, 'card', 'R', 20, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(166, 'card', 'O', 20, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(167, 'card', 'O', 20, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(168, 'group', 'O', 20, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(169, 'card', 'P', 21, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(170, 'card', 'R', 21, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(171, 'card', 'O', 21, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(172, 'card', 'O', 21, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(173, 'group', 'O', 21, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(179, 'card', 'P', 2, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(180, 'card', 'R', 2, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(181, 'card', 'O', 2, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(182, 'card', 'O', 2, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(183, 'group', 'O', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(202, 'card', 'P', 7, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(203, 'card', 'R', 7, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(204, 'card', 'O', 7, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(205, 'card', 'O', 7, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(206, 'group', 'O', 7, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(207, 'card', 'P', 8, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(208, 'card', 'R', 8, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(209, 'card', 'O', 8, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(210, 'card', 'O', 8, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(211, 'group', 'O', 8, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(212, 'card', 'P', 9, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(213, 'card', 'R', 9, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(214, 'card', 'O', 9, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(215, 'card', 'O', 9, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(216, 'group', 'O', 9, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(217, 'card', 'P', 22, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(218, 'card', 'R', 22, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(219, 'card', 'O', 22, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(220, 'card', 'O', 22, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(221, 'group', 'O', 22, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(222, 'card', 'P', 23, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(223, 'card', 'R', 23, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(224, 'card', 'O', 23, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(225, 'card', 'O', 23, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(226, 'group', 'O', 23, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(227, 'card', 'P', 24, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(228, 'card', 'R', 24, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(229, 'card', 'O', 24, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(230, 'card', 'O', 24, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(231, 'group', 'O', 24, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(232, 'card', 'P', 25, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(233, 'card', 'R', 25, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(234, 'card', 'O', 25, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(235, 'card', 'O', 25, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(236, 'group', 'O', 25, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(237, 'card', 'P', 26, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(238, 'card', 'R', 26, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(239, 'card', 'O', 26, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(240, 'card', 'O', 26, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(241, 'group', 'O', 26, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(242, 'card', 'P', 27, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(243, 'card', 'R', 27, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(244, 'card', 'O', 27, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(245, 'card', 'O', 27, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(246, 'group', 'O', 27, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(247, 'card', 'P', 28, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(248, 'card', 'R', 28, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(249, 'card', 'O', 28, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(250, 'card', 'O', 28, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(251, 'group', 'O', 28, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(252, 'card', 'P', 29, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(253, 'card', 'R', 29, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(254, 'card', 'O', 29, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(255, 'card', 'O', 29, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(256, 'group', 'O', 29, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(263, 'card', 'P', 30, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(264, 'card', 'R', 30, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(265, 'card', 'O', 30, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(266, 'card', 'O', 30, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(267, 'card', 'O', 30, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(268, 'group', 'O', 30, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(269, 'card', 'P', 31, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(270, 'card', 'R', 31, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(271, 'card', 'O', 31, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(272, 'card', 'O', 31, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(273, 'card', 'O', 31, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(274, 'group', 'O', 31, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(275, 'card', 'P', 32, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(276, 'card', 'R', 32, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(277, 'card', 'O', 32, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(278, 'card', 'O', 32, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(279, 'card', 'O', 32, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(280, 'group', 'O', 32, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(281, 'card', 'P', 33, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(282, 'card', 'R', 33, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(283, 'card', 'O', 33, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(284, 'card', 'O', 33, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(285, 'card', 'O', 33, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(286, 'group', 'O', 33, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(287, 'card', 'P', 34, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(288, 'card', 'R', 34, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(289, 'card', 'O', 34, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(290, 'card', 'O', 34, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(291, 'card', 'O', 34, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(292, 'group', 'O', 34, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(293, 'card', 'P', 35, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(294, 'card', 'R', 35, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(295, 'card', 'O', 35, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(296, 'card', 'O', 35, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(297, 'card', 'O', 35, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(298, 'group', 'O', 35, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(299, 'card', 'P', 3, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(300, 'card', 'R', 3, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(301, 'card', 'O', 3, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(302, 'group', 'O', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(303, 'card', 'P', 4, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(304, 'card', 'R', 4, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(305, 'card', 'O', 4, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(306, 'card', 'O', 4, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(307, 'group', 'O', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(308, 'card', 'P', 5, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(309, 'card', 'R', 5, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(310, 'card', 'O', 5, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(311, 'group', 'O', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(312, 'card', 'P', 6, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(313, 'card', 'R', 6, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(314, 'card', 'O', 6, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(315, 'card', 'O', 6, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(316, 'group', 'O', 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(317, 'card', 'P', 36, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(318, 'card', 'R', 36, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(319, 'card', 'O', 36, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(320, 'card', 'O', 36, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(321, 'card', 'O', 36, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(322, 'group', 'O', 36, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(323, 'card', 'P', 37, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(324, 'card', 'R', 37, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(325, 'card', 'O', 37, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(326, 'card', 'O', 37, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(327, 'card', 'O', 37, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(328, 'group', 'O', 37, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(329, 'card', 'P', 38, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(330, 'card', 'R', 38, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(331, 'card', 'O', 38, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(332, 'card', 'O', 38, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(333, 'card', 'O', 38, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(334, 'group', 'O', 38, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(335, 'card', 'P', 39, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(336, 'card', 'R', 39, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(337, 'card', 'O', 39, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(338, 'card', 'O', 39, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(339, 'card', 'O', 39, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(340, 'group', 'O', 39, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(356, 'card', 'O', 43, 1, NULL, 1, 'l', 'l', NULL, NULL, NULL, NULL),
(357, 'card', 'O', 43, 2, NULL, 2, 'l', 'l', NULL, NULL, NULL, NULL),
(358, 'card', 'O', 43, 3, NULL, 3, 'l', 'l', NULL, NULL, NULL, NULL),
(359, 'card', 'O', 43, 4, NULL, 4, 'l', 'l', NULL, NULL, NULL, NULL),
(360, 'card', 'O', 44, 1, NULL, 1, 'e', 'e', NULL, NULL, NULL, NULL),
(361, 'card', 'O', 44, 2, NULL, 2, 'e', 'e', NULL, NULL, NULL, NULL),
(362, 'card', 'O', 44, 3, NULL, 3, 'e', 'e', NULL, NULL, NULL, NULL),
(363, 'card', 'O', 44, 4, NULL, 4, 'e', 'e', NULL, NULL, NULL, NULL),
(364, 'card', 'O', 45, 1, NULL, 1, 'a', 'v', NULL, NULL, NULL, NULL),
(365, 'card', 'O', 45, 2, NULL, 2, 'a', 'v', NULL, NULL, NULL, NULL),
(366, 'card', 'O', 45, 3, NULL, 3, 'a', 'v', NULL, NULL, NULL, NULL),
(367, 'card', 'O', 45, 4, NULL, 4, 'a', 'v', NULL, NULL, NULL, NULL),
(368, 'card', 'O', 46, 1, NULL, 1, 'v', 'a', NULL, NULL, NULL, NULL),
(369, 'card', 'O', 46, 2, NULL, 2, 'v', 'a', NULL, NULL, NULL, NULL),
(370, 'card', 'O', 46, 3, NULL, 3, 'v', 'a', NULL, NULL, NULL, NULL),
(371, 'card', 'O', 46, 4, NULL, 4, 'v', 'a', NULL, NULL, NULL, NULL),
(372, 'card', 'O', 47, 1, NULL, 1, 'l', 'i', NULL, NULL, NULL, NULL),
(373, 'card', 'O', 47, 2, NULL, 2, 'l', 'i', NULL, NULL, NULL, NULL),
(374, 'card', 'O', 47, 3, NULL, 3, 'l', 'i', NULL, NULL, NULL, NULL),
(375, 'card', 'O', 47, 4, NULL, 4, 'l', 'i', NULL, NULL, NULL, NULL),
(416, 'card', 'P', 40, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(417, 'card', 'R', 40, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(418, 'card', 'O', 40, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(419, 'card', 'O', 40, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(420, 'card', 'O', 40, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(421, 'group', 'O', 40, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(458, 'card', 'O', 42, 1, NULL, 1, 'e, i, a, v, l', 'l, i, a, v, e', NULL, NULL, NULL, NULL),
(459, 'card', 'O', 42, 2, NULL, 2, 'e, i, a, v, l', 'l, i, a, v, e', NULL, NULL, NULL, NULL),
(460, 'card', 'O', 42, 3, NULL, 3, 'e, i, a, v, l', 'l, i, a, v, e', NULL, NULL, NULL, NULL),
(461, 'card', 'O', 42, 4, NULL, 4, 'e, i, a, v, l', 'l, i, a, v, e', NULL, NULL, NULL, NULL),
(520, 'card', 'O', 61, 3, NULL, NULL, 'i, v, a', 'l', NULL, NULL, NULL, NULL),
(521, 'card', 'O', 60, 2, NULL, NULL, 'e, i, v', 'l', NULL, NULL, NULL, NULL),
(522, 'card', 'O', 59, 1, NULL, NULL, 'e, a, i', 'l', NULL, NULL, NULL, NULL),
(523, 'card', 'O', 58, 1, NULL, NULL, 'a, v, e', 'l', NULL, NULL, NULL, NULL),
(524, 'card', 'O', 57, 4, NULL, NULL, 'l, i, v', 'l', NULL, NULL, NULL, NULL),
(525, 'card', 'O', 56, 3, NULL, NULL, 'l, a, i', 'l', NULL, NULL, NULL, NULL),
(526, 'card', 'O', 55, 2, NULL, NULL, 'l, v, a', 'l', NULL, NULL, NULL, NULL),
(527, 'card', 'O', 54, 2, NULL, NULL, 'l, i, e', 'l', NULL, NULL, NULL, NULL),
(528, 'card', 'O', 53, 1, NULL, NULL, 'l, v, e', 'l', NULL, NULL, NULL, NULL),
(529, 'card', 'O', 52, 1, NULL, NULL, 'v', 'l, a, e', NULL, NULL, NULL, NULL),
(530, 'card', 'O', 51, 1, NULL, NULL, 'a', 'l', NULL, NULL, NULL, NULL),
(531, 'card', 'O', 50, 1, NULL, NULL, 'l', 'l, e', NULL, NULL, NULL, NULL),
(532, 'card', 'O', 49, 1, NULL, NULL, 'e, a, l', 'l, a, e', NULL, NULL, NULL, NULL),
(546, 'card', 'P', 79, 1, NULL, NULL, 'l, e, i, v, a', 'l, e, a, v, i', NULL, NULL, NULL, NULL),
(547, 'card', 'P', 79, 2, NULL, NULL, 'l, e, i, v, a', 'l, e, a, v, i', NULL, NULL, NULL, NULL),
(550, 'card', 'O', 77, NULL, NULL, NULL, NULL, NULL, 'Y', NULL, NULL, NULL),
(557, 'card', 'O', 78, NULL, NULL, NULL, NULL, NULL, 'Y', NULL, NULL, NULL),
(558, 'card', 'O', 76, 2, NULL, 2, 'l, e, i', 'e, a, l', 'Y', NULL, NULL, NULL),
(559, 'card', 'O', 75, 2, NULL, 2, 'l, i, v', 'l, e, i', 'Y', NULL, NULL, NULL),
(561, 'card', 'O', 74, 1, NULL, 2, 'i, v, a', 'l, i, v', 'N', NULL, NULL, NULL),
(562, 'card', 'O', 73, 1, NULL, 1, 'a, v, e', 'i, v, a', 'Y', NULL, NULL, NULL),
(563, 'card', 'O', 71, 2, NULL, 3, 'i, l', 'v, l', 'N', NULL, NULL, NULL),
(564, 'card', 'O', 70, 4, NULL, 4, 'l, i', 'e, l', 'Y', NULL, NULL, NULL),
(565, 'card', 'O', 69, 2, NULL, 2, 'v, a', 'l, i', 'Y', NULL, NULL, NULL),
(566, 'card', 'O', 68, 1, NULL, 2, 'a, e', 'i, v', 'N', NULL, NULL, NULL),
(567, 'card', 'O', 67, 2, NULL, 2, 'e, l', 'v, a', 'Y', NULL, NULL, NULL),
(568, 'card', 'O', 66, 3, NULL, 3, 'i', 'l', 'Y', NULL, NULL, NULL),
(569, 'card', 'O', 65, 4, NULL, 1, 'v', 'i', 'N', NULL, NULL, NULL),
(570, 'card', 'O', 64, 3, NULL, 3, 'a', 'v', 'Y', NULL, NULL, NULL),
(571, 'card', 'O', 63, 2, NULL, 1, 'e', 'a', 'N', NULL, NULL, NULL),
(572, 'card', 'O', 62, 1, NULL, 1, 'l', 'e', 'Y', NULL, NULL, NULL),
(581, 'card', 'O', 48, 1, NULL, 1, 'i', 'l', NULL, NULL, NULL, NULL),
(582, 'card', 'O', 48, 2, NULL, 2, 'i', 'l', NULL, NULL, NULL, NULL),
(583, 'card', 'O', 48, 3, NULL, 3, 'i', 'l', NULL, NULL, NULL, NULL),
(584, 'card', 'O', 48, 4, NULL, 4, 'i', 'l', NULL, NULL, NULL, NULL),
(585, 'card', 'O', 72, 1, NULL, 2, 'e, a, l', 'a, v, e', 'N', NULL, NULL, NULL),
(587, 'card', 'P', 81, 1, NULL, NULL, 'l, i, a, v, e', 'l', NULL, 'typing', 'Lakota [bold] red and [underlined] blue and [italic] orange', NULL),
(588, 'card', 'O', 81, 1, NULL, NULL, 'l, i, a, v, e', 'l', NULL, 'typing', 'bold', 1),
(589, 'card', 'O', 81, 1, NULL, NULL, 'l, i, a, v, e', 'l', NULL, 'typing', 'underlined', 2),
(590, 'card', 'O', 81, 1, NULL, NULL, 'l, i, a, v, e', 'l', NULL, 'typing', 'italic', 3),
(592, 'card', 'P', 82, 2, NULL, NULL, 'e, i, a, v, l', 'l', NULL, 'mcq', 'Lakota [bold] green and [underlined] violet and [italic] brown', NULL),
(593, 'card', 'O', 82, 2, NULL, NULL, 'e, i, a, v, l', 'l', NULL, 'mcq', 'bold', 1),
(594, 'card', 'O', 82, 2, NULL, NULL, 'e, i, a, v, l', 'l', NULL, 'mcq', 'underlined', 2),
(595, 'card', 'O', 82, 2, NULL, NULL, 'e, i, a, v, l', 'l', NULL, 'mcq', 'italic', 3),
(596, 'card', 'O', 82, 2, NULL, NULL, 'e, i, a, v, l', 'l', NULL, 'mcq', 'struckthrough', NULL),
(597, 'card', 'O', 82, 2, NULL, NULL, 'e, i, a, v, l', 'l', NULL, 'mcq', 'hyperlinked', NULL),
(598, 'card', 'O', 82, 2, NULL, NULL, 'e, i, a, v, l', 'l', NULL, 'mcq', 'capitalized', NULL),
(610, 'card', 'P', 86, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'This [dog] is really [old]', NULL),
(611, 'card', 'O', 86, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'dog', 1),
(612, 'card', 'O', 86, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'old', 2),
(618, 'card', 'O', 87, 1, NULL, NULL, 'l, e, i, v, a', 'l', NULL, NULL, NULL, NULL),
(619, 'card', 'P', 88, 1, NULL, NULL, 'l, e, a, v, i', 'l', NULL, NULL, NULL, NULL),
(620, 'card', 'P', 88, 2, NULL, NULL, 'l, e, a, v, i', 'l', NULL, NULL, NULL, NULL),
(621, 'card', 'P', 88, 3, NULL, NULL, 'l, e, a, v, i', 'l', NULL, NULL, NULL, NULL),
(623, 'card', 'P', 89, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(624, 'card', 'O', 89, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(625, 'card', 'O', 89, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(626, 'card', 'O', 89, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(632, 'card', 'O', 91, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(633, 'card', 'O', 91, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(634, 'card', 'O', 91, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(635, 'card', 'O', 91, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(639, 'card', 'O', 92, 1, NULL, 1, 'e, i, a, v, l', 'e, i, a, v, l', NULL, NULL, NULL, NULL),
(640, 'card', 'O', 92, 2, NULL, 2, 'e, i, a, v, l', 'e, i, a, v, l', NULL, NULL, NULL, NULL),
(641, 'card', 'O', 92, 3, NULL, 3, 'e, i, a, v, l', 'e, i, a, v, l', NULL, NULL, NULL, NULL),
(647, 'card', 'P', 93, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(648, 'card', 'O', 93, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(649, 'card', 'O', 93, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(650, 'card', 'O', 93, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(651, 'card', 'P', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(652, 'card', 'R', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(653, 'card', 'O', 1, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(654, 'card', 'O', 1, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(655, 'group', 'O', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(669, 'card', 'P', 94, 11, NULL, 2, 'a', 'i', NULL, NULL, NULL, NULL),
(670, 'card', 'O', 94, NULL, NULL, 4, NULL, 'i', NULL, NULL, NULL, NULL),
(671, 'card', 'O', 94, NULL, NULL, 1, NULL, 'i', NULL, NULL, NULL, NULL),
(672, 'card', 'O', 94, NULL, NULL, 3, NULL, 'i', NULL, NULL, NULL, NULL),
(674, 'card', 'P', 95, 1, NULL, NULL, 'i', 'l', NULL, 'typing', '[Lakota] bold red [and] underlined blue [and] italic orange', NULL),
(675, 'card', 'O', 95, 1, NULL, NULL, 'i', 'l', NULL, 'typing', 'Lakota', 1),
(676, 'card', 'O', 95, 1, NULL, NULL, 'i', 'l', NULL, 'typing', 'and', 2),
(677, 'card', 'O', 95, 1, NULL, NULL, 'i', 'l', NULL, 'typing', 'and', 3),
(722, 'card', 'P', 85, 3, NULL, NULL, 'e, i, a, v, l', 'l', NULL, NULL, 'Lakota [bold] brown and [underlined] pink and [italic] gray', NULL),
(723, 'card', 'O', 85, 3, NULL, NULL, 'e, i, a, v, l', 'l', NULL, NULL, 'bold', 1),
(724, 'card', 'O', 85, 3, NULL, NULL, 'e, i, a, v, l', 'l', NULL, NULL, 'underlined', 2),
(725, 'card', 'O', 85, 3, NULL, NULL, 'e, i, a, v, l', 'l', NULL, NULL, 'italic', 3),
(727, 'card', 'P', 98, NULL, NULL, 1, NULL, 'a,l', NULL, NULL, NULL, NULL),
(728, 'card', 'O', 98, NULL, NULL, 2, NULL, 'a,l', NULL, NULL, NULL, NULL),
(729, 'card', 'O', 98, NULL, NULL, 3, NULL, 'a,l', NULL, NULL, NULL, NULL),
(730, 'card', 'O', 98, NULL, NULL, 4, NULL, 'a,l', NULL, NULL, NULL, NULL),
(734, 'card', 'P', 99, 7, NULL, 3, 'i,a', 'v,e', NULL, NULL, NULL, NULL),
(735, 'card', 'O', 99, NULL, NULL, 4, NULL, 'a,e', NULL, NULL, NULL, NULL),
(739, 'card', 'O', 101, 1, NULL, 2, 'e, a', 'a, i', 'N', NULL, NULL, NULL),
(740, 'card', 'P', 90, 1, NULL, 1, 'i,a,l', 'e,i,a,v,l', NULL, NULL, NULL, NULL),
(741, 'card', 'O', 90, NULL, NULL, 2, NULL, 'e,i,a,v,l', NULL, NULL, NULL, NULL),
(742, 'card', 'O', 90, NULL, NULL, 3, NULL, 'e,i,a,v,l', NULL, NULL, NULL, NULL),
(743, 'card', 'O', 90, NULL, NULL, 4, NULL, 'e,i,a,v,l', NULL, NULL, NULL, NULL),
(744, 'card', 'P', 96, 1, NULL, NULL, 'l, i', 'l', NULL, 'mcq', '[l]or[e] bold red, [and] underlined blue, [and] italic [a or]?', NULL),
(745, 'card', 'O', 96, 1, NULL, NULL, 'l, i', 'l', NULL, 'mcq', 'l', 1),
(746, 'card', 'O', 96, 1, NULL, NULL, 'l, i', 'l', NULL, 'mcq', 'e', 2),
(747, 'card', 'O', 96, 1, NULL, NULL, 'l, i', 'l', NULL, 'mcq', 'and', 3),
(748, 'card', 'O', 96, 1, NULL, NULL, 'l, i', 'l', NULL, 'mcq', 'and', 4),
(749, 'card', 'O', 96, 1, NULL, NULL, 'l, i', 'l', NULL, 'mcq', 'a or', 5),
(750, 'card', 'O', 96, 1, NULL, NULL, 'l, i', 'l', NULL, 'mcq', 'ȟo', NULL),
(757, 'group', 'P', 102, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(758, 'group', 'P', 102, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(759, 'group', 'O', 102, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(760, 'group', 'O', 102, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(761, 'group', 'O', 102, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(762, 'group', 'O', 102, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(763, 'group', 'O', 103, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(764, 'group', 'O', 103, 1, NULL, 1, 'l, a', 'e, i', NULL, NULL, NULL, NULL),
(765, 'group', 'O', 103, 2, NULL, 2, 'l, a', 'e, i', NULL, NULL, NULL, NULL),
(766, 'group', 'O', 103, 3, NULL, 3, 'l, a', 'e, i', NULL, NULL, NULL, NULL),
(767, 'group', 'O', 103, 4, NULL, 4, 'l, a', 'e, i', NULL, NULL, NULL, NULL),
(768, 'group', 'O', 103, 5, NULL, 5, 'l, a', 'e, i', NULL, NULL, NULL, NULL),
(769, 'card', 'P', 104, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(770, 'card', 'R', 104, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(771, 'card', 'O', 104, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(772, 'card', 'O', 104, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(773, 'group', 'O', 104, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `id` int(11) UNSIGNED NOT NULL,
  `upload_user_id` int(11) UNSIGNED DEFAULT NULL,
  `name` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `description` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `format` varchar(255) DEFAULT NULL,
  `type` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `file_name` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `aws_link` varchar(500) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `files`
--

INSERT INTO `files` (`id`, `upload_user_id`, `name`, `description`, `format`, `type`, `file_name`, `aws_link`, `created`, `modified`) VALUES
(3, 1, 'image_01', 'image_01', 'jpeg', 'image', 'image_01.jpg', NULL, '2024-09-15 10:24:18', '2024-09-15 10:24:18'),
(4, 1, 'path_icon_01', 'path_icon_01', 'png', 'image', 'path_icon_01.png', NULL, '2024-09-15 10:26:42', '2024-09-15 10:26:42'),
(5, 1, 'level_icon_01', 'level_icon_01', 'png', 'image', 'level_icon_01.png', NULL, '2024-09-15 10:28:34', '2024-09-15 10:28:34'),
(6, 1, 'lakota_bold_red_underlined_blue_italic_orange', 'lakota_bold_red_underlined_blue_italic_orange', 'mpeg', 'audio', 'lakota_bold_red_underlined_blue_italic_orange.mp3', NULL, '2024-09-15 10:37:29', '2024-09-15 10:37:29'),
(7, 1, 'venice_5s', 'venice_5s', 'mp4', 'video', 'venice_5s.mp4', NULL, '2024-09-15 10:43:33', '2024-09-15 10:43:33'),
(8, 1, 'recording_02', 'recording_02', 'mpeg', 'audio', 'recording_02.mp3', NULL, '2024-09-16 12:37:00', '2024-09-16 12:37:00'),
(9, 1, 'recording_03', 'recording_03', 'mpeg', 'audio', 'recording_03.mp3', NULL, '2024-09-16 12:37:47', '2024-09-16 12:37:47'),
(10, 1, 'old', 'old', 'jpeg', 'image', 'old.jpg', NULL, '2024-09-16 12:47:45', '2024-09-16 12:47:45'),
(11, 1, 'old', 'old', 'mpeg', 'audio', 'old.mp3', NULL, '2024-09-16 12:47:45', '2024-09-16 12:47:45'),
(12, 1, 'sample_video', 'sample_video', 'mp4', 'video', 'sample_video.mp4', NULL, '2024-09-16 12:48:34', '2024-09-16 12:48:34'),
(13, 1, 'big_little_car', 'big_little_car', 'png', 'image', 'big_little_car.png', NULL, '2024-09-16 12:53:08', '2024-09-16 12:53:08'),
(14, 1, 'panda', 'panda', 'jpeg', 'image', 'panda.png', NULL, '2024-09-16 12:56:58', '2024-09-16 12:56:58'),
(15, 1, 'kaleidoscope', 'kaleidoscope', 'mp4', 'video', 'kaleidoscope.mp4', NULL, '2024-09-16 12:58:32', '2024-09-16 12:58:32'),
(16, 1, 'bold_purple', 'bold_purple', 'mpeg', 'audio', 'bold_purple.mp3', NULL, '2024-09-18 07:39:27', '2024-09-18 07:39:27'),
(17, 1, 'small_dog', 'small_dog', 'jpeg', 'image', 'small_dog.jpeg', NULL, '2024-09-18 07:40:09', '2024-09-18 07:40:09'),
(18, 1, 'bridge_collapse', 'bridge_collapse', 'mp4', 'video', 'bridge_collapse.mp4', NULL, '2024-09-18 07:58:36', '2024-09-18 07:58:36');

-- --------------------------------------------------------

--
-- Table structure for table `forums`
--

CREATE TABLE `forums` (
  `id` int(10) UNSIGNED NOT NULL,
  `path_id` int(10) UNSIGNED DEFAULT NULL,
  `level_id` int(10) UNSIGNED DEFAULT NULL,
  `unit_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE latin1_general_ci NOT NULL,
  `subtitle` text COLLATE latin1_general_ci NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `forums`
--

INSERT INTO `forums` (`id`, `path_id`, `level_id`, `unit_id`, `title`, `subtitle`, `created`, `modified`) VALUES
(2, 1, 2, NULL, 'Lessons by Unit', 'Lessons by Unit', '2024-09-15 10:27:14', '2024-09-15 10:27:14'),
(3, 1, 2, 1, 'Lessons', 'Lessons', '2024-09-15 10:27:40', '2024-09-15 10:27:40'),
(4, 1, 3, NULL, 'Lessons by Unit', 'Lessons by Unit', '2024-09-15 10:29:05', '2024-09-15 10:29:05'),
(5, 1, 2, 2, 'Lessons (English + Audio | Video | Image)', 'Lessons (English + Audio | Video | Image)', '2024-09-15 13:51:15', '2024-09-15 13:51:15'),
(6, 1, 2, 1, 'Lessons (Lakota + English | Audio | Video | Image)', 'Lessons (Lakota + English | Audio | Video | Image)', '2024-09-15 13:51:25', '2024-09-15 13:51:25'),
(7, 1, 2, 3, 'Lessons (Audio + Video | Image)', 'Lessons (Audio + Video | Image)', '2024-09-15 15:24:52', '2024-09-15 15:24:52'),
(8, 1, 2, 4, 'Lessons (Video + Image)', 'Lessons (Video + Image)', '2024-09-15 15:25:24', '2024-09-15 15:25:24'),
(9, 1, 2, 5, 'Lessons (Image)', 'Lessons (Image)', '2024-09-15 15:25:57', '2024-09-15 15:25:57'),
(10, 1, 2, 4, 'Lessons (1 Block Card, Video + Image)', 'Lessons (1 Block Card, Video + Image)', '2024-09-15 15:28:58', '2024-09-15 15:28:58'),
(11, 1, 2, 5, 'Lessons (1 Block Card, Image)', 'Lessons (1 Block Card, Image)', '2024-09-15 15:29:03', '2024-09-15 15:29:03'),
(12, 1, 2, 3, 'Lessons (1 Block Card, Audio + Video | Image)', 'Lessons (1 Block Card, Audio + Video | Image)', '2024-09-15 15:29:10', '2024-09-15 15:29:10'),
(13, 1, 2, 2, 'Lessons (1 Block Card, English + Audio | Video | Image)', 'Lessons (1 Block Card, English + Audio | Video | Image)', '2024-09-15 15:29:15', '2024-09-15 15:29:15'),
(14, 1, 2, 1, 'Lessons (1 Block Card, Lakota + English | Audio | Video | Image)', 'Lessons (1 Block Card, Lakota + English | Audio | Video | Image)', '2024-09-15 15:29:27', '2024-09-15 15:29:27'),
(15, 1, 4, NULL, 'Lessons by Unit', 'Lessons by Unit', '2024-09-15 15:36:27', '2024-09-15 15:36:27'),
(16, 1, 2, 1, 'Lessons (1 Block Card, 1 Field)', 'Lessons (1 Block Card, 1 Field)', '2024-09-15 16:20:44', '2024-09-15 16:20:44'),
(17, 1, 2, 2, 'Lessons (1 Block Card, 2 Field)', 'Lessons (1 Block Card, 2 Field)', '2024-09-15 16:20:56', '2024-09-15 16:20:56'),
(18, 1, 2, 3, 'Lessons (1 Block Card, 3 Field)', 'Lessons (1 Block Card, 3 Field)', '2024-09-15 16:21:13', '2024-09-15 16:21:13'),
(19, 1, 2, 4, 'Lessons (1 Block Card, 4 Fields)', 'Lessons (1 Block Card, 4 Fields)', '2024-09-15 16:21:35', '2024-09-15 16:21:35'),
(20, 1, 2, 2, 'Lessons (1 Block Card, 2 Fields)', 'Lessons (1 Block Card, 2 Fields)', '2024-09-15 16:21:56', '2024-09-15 16:21:56'),
(21, 1, 2, 3, 'Lessons (1 Block Card, 3 Fields)', 'Lessons (1 Block Card, 3 Fields)', '2024-09-15 16:22:02', '2024-09-15 16:22:02'),
(22, 1, 2, 5, 'Lessons (1 Block Card, 5 Fields)', 'Lessons (1 Block Card, 5 Fields)', '2024-09-15 16:24:28', '2024-09-15 16:24:28'),
(23, 1, 4, 6, 'Lessons (2 Blocks Card, 1 Field)', 'Lessons (2 Blocks Card, 1 Field)', '2024-09-15 16:42:57', '2024-09-15 16:42:57'),
(24, 1, 4, 7, 'Lessons (2 Blocks Card, 2 Fields)', 'Lessons (2 Blocks Card, 2 Fields)', '2024-09-15 16:43:26', '2024-09-15 16:43:26'),
(25, 1, 4, 8, 'Lessons (2 Blocks Card, 3 Fields)', 'Lessons (2 Blocks Card, 3 Fields)', '2024-09-15 16:43:40', '2024-09-15 16:43:40'),
(26, 1, 4, 9, 'Lessons (2 Blocks Card, 4 Fields)', 'Lessons (2 Blocks Card, 4 Fields)', '2024-09-15 16:43:52', '2024-09-15 16:43:52'),
(27, 1, 4, 10, 'Lessons (2 Blocks Card, 5 Fields)', 'Lessons (2 Blocks Card, 5 Fields)', '2024-09-15 16:44:23', '2024-09-15 16:44:23'),
(28, 1, 5, NULL, 'Lessons by Unit', 'Lessons by Unit', '2024-09-15 16:46:08', '2024-09-15 16:46:08'),
(29, 1, 5, 11, 'Lessons (3 Blocks Card, 1 Field)', 'Lessons (3 Blocks Card, 1 Field)', '2024-09-16 12:32:54', '2024-09-16 12:32:54'),
(30, 1, 5, 12, 'Lessons (3 Blocks Card, 2 Fields)', 'Lessons (3 Blocks Card, 2 Fields)', '2024-09-16 12:33:09', '2024-09-16 12:33:09'),
(31, 1, 5, 13, 'Lessons (3 Blocks Card, 3 Fields)', 'Lessons (3 Blocks Card, 3 Fields)', '2024-09-16 12:33:16', '2024-09-16 12:33:16'),
(32, 1, 5, 14, 'Lessons (3 Blocks Card, 4 Fields)', 'Lessons (3 Blocks Card, 4 Fields)', '2024-09-16 12:33:23', '2024-09-16 12:33:23'),
(33, 1, 5, 15, 'Lessons (3 Blocks Card, 5 Fields)', 'Lessons (3 Blocks Card, 5 Fields)', '2024-09-16 12:33:31', '2024-09-16 12:33:31'),
(34, 1, 3, 16, 'MCQ, Card, Field1 -> Field1', 'MCQ, Card, Field1 -> Field1', '2024-09-16 18:06:59', '2024-09-16 18:06:59'),
(35, 1, 3, 16, 'MCQ, 1 Card -> 2 Cards, 1 Field', 'MCQ, 1 Card -> 2 Cards, 1 Field', '2024-09-16 18:22:52', '2024-09-16 18:22:52'),
(36, 1, 3, 16, 'MCQ, 1 Card -> 3 Cards, 1 Field', 'MCQ, 1 Card -> 3 Cards, 1 Field', '2024-09-17 15:39:28', '2024-09-17 15:39:28'),
(37, 1, 3, 17, 'MCQ, 1 Card -> 3 Cards, 2 Fields', 'MCQ, 1 Card -> 3 Cards, 2 Fields', '2024-09-17 15:39:48', '2024-09-17 15:39:48'),
(38, 1, 3, 18, 'MCQ, 1 Card -> 3 Cards, 3 Fields', 'MCQ, 1 Card -> 3 Cards, 3 Fields', '2024-09-18 07:30:05', '2024-09-18 07:30:05'),
(39, 1, 3, 19, 'MCQ, 1 Card -> 4 Cards, 5 Fields', 'MCQ, 1 Card -> 4 Cards, 5 Fields', '2024-09-18 11:10:57', '2024-09-18 11:10:57'),
(40, 1, 6, NULL, 'Lessons by Unit', 'Lessons by Unit', '2024-09-27 18:40:12', '2024-09-27 18:40:12'),
(41, 1, 6, 20, 'MTP, 4 Cards, 5 Fields', 'MTP, 4 Cards, 5 Fields', '2024-09-27 18:40:43', '2024-09-27 18:40:43'),
(42, 1, 6, 21, 'MTP, 4 Cards, 1 Field', 'MTP, 4 Cards, 1 Field', '2024-09-27 19:12:10', '2024-09-27 19:12:10'),
(43, 1, 7, NULL, 'Lessons by Unit', 'Lessons by Unit', '2024-10-29 16:45:58', '2024-10-29 16:45:58'),
(44, 1, 7, 22, 'Anagram - Card, 3 Fields', 'Anagram - Card, 3 Fields', '2024-10-29 16:49:06', '2024-10-29 16:49:06'),
(45, 1, 7, 23, 'Anagram - Card, 1 Field', 'Anagram - Card, 1 Field', '2024-10-29 17:01:13', '2024-10-29 17:01:13'),
(46, 1, 8, NULL, 'Lessons by Unit', 'Lessons by Unit', '2024-11-02 16:20:35', '2024-11-02 16:20:35'),
(47, 1, 8, 24, 'TrueFalse, Cards, 1 Field', 'TrueFalse, Cards, 1 Field', '2024-11-02 16:20:53', '2024-11-02 16:20:53'),
(48, 1, 8, 25, 'True False, Cards, 2 Fields', 'True False, Cards, 2 Fields', '2024-11-02 16:21:26', '2024-11-02 16:21:26'),
(49, 1, 8, 24, 'True False, Cards, 1 Field', 'True False, Cards, 1 Field', '2024-11-02 16:21:34', '2024-11-02 16:21:34'),
(50, 1, 8, 26, 'True False, Cards, 3 Fields', 'True False, Cards, 3 Fields', '2024-11-02 16:22:03', '2024-11-02 16:22:03'),
(51, 1, 8, 27, 'True False, HTML', 'True False, HTML', '2024-11-03 22:16:14', '2024-11-03 22:16:14'),
(52, 1, 8, 28, 'True False, Card Group', 'True False, Card Group', '2024-11-03 22:16:42', '2024-11-03 22:16:42'),
(53, 1, 9, NULL, 'Lessons by Unit', 'Lessons by Unit', '2024-11-06 03:57:17', '2024-11-06 03:57:17'),
(54, 1, 9, 29, 'Fill-in Typing Cards', 'Fill-in Typing Cards', '2024-11-06 03:57:34', '2024-11-06 03:57:34'),
(55, 1, 10, NULL, 'Lessons by Unit', 'Lessons by Unit', '2024-11-07 19:50:41', '2024-11-07 19:50:41'),
(56, 1, 10, 30, 'Fill-in MCQ Cards', 'Fill-in MCQ Cards', '2024-11-07 19:51:06', '2024-11-07 19:51:06'),
(58, 1, 9, 32, 'Fill-in Typing Custom', 'Fill-in Typing Custom', '2024-11-07 19:52:11', '2024-11-07 19:52:11'),
(60, 1, 7, 33, 'Anagram - Custom/Card', 'Anagram - Custom/Card', '2024-11-08 19:24:15', '2024-11-08 19:24:15'),
(61, 1, 7, 34, 'Anagram - Custom/Card Group', 'Anagram - Custom/Card Group', '2024-11-08 19:24:26', '2024-11-08 19:24:26'),
(62, 1, 3, 35, 'MCQ, Custom', 'MCQ, Custom', '2024-11-09 00:38:41', '2024-11-09 00:38:41'),
(63, 1, 6, 36, 'MTP, Custom', 'MTP, Custom', '2024-11-09 00:57:11', '2024-11-09 00:57:11'),
(64, 1, 3, 37, 'MCQ, Card Groups', 'MCQ, Card Groups', '2025-05-07 23:03:13', '2025-05-07 23:03:13'),
(65, 1, 6, 38, 'MTP, Card Group', 'MTP, Card Group', '2025-05-07 23:16:34', '2025-05-07 23:16:34');

-- --------------------------------------------------------

--
-- Table structure for table `forum_flags`
--

CREATE TABLE `forum_flags` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `post_id` int(11) UNSIGNED DEFAULT NULL,
  `flag` enum('R') COLLATE latin1_general_ci DEFAULT 'R',
  `entry_time` datetime DEFAULT NULL,
  `report_type` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forum_flag_reasons`
--

CREATE TABLE `forum_flag_reasons` (
  `id` int(11) UNSIGNED NOT NULL,
  `reason` varchar(255) COLLATE latin1_general_ci NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci ROW_FORMAT=COMPACT;

--
-- Dumping data for table `forum_flag_reasons`
--

INSERT INTO `forum_flag_reasons` (`id`, `reason`, `created`, `modified`) VALUES
(1, 'profanity', '2018-05-24 00:00:00', '2018-05-24 00:00:00'),
(2, 'off topic', '2018-05-24 00:00:00', '2018-05-24 00:00:00'),
(3, 'abusive', '2018-05-24 00:00:00', '2018-05-24 00:00:00'),
(4, 'other (explain)', '2018-05-24 00:00:00', '2018-05-24 00:00:00'),
(5, 'shadow banned', '2022-08-23 18:37:02', '2022-08-23 18:37:02');

-- --------------------------------------------------------

--
-- Table structure for table `forum_posts`
--

CREATE TABLE `forum_posts` (
  `id` int(11) UNSIGNED NOT NULL,
  `parent_id` int(11) UNSIGNED DEFAULT NULL,
  `forum_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `flag_id` int(11) UNSIGNED DEFAULT NULL,
  `title` varchar(500) CHARACTER SET utf8 DEFAULT NULL,
  `content` text CHARACTER SET utf8,
  `audio` int(11) UNSIGNED DEFAULT NULL,
  `sticky` enum('Y','N') COLLATE latin1_general_ci NOT NULL DEFAULT 'N',
  `is_hide` enum('Y','N') COLLATE latin1_general_ci NOT NULL DEFAULT 'N',
  `entry_time` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forum_post_viewers`
--

CREATE TABLE `forum_post_viewers` (
  `id` int(11) UNSIGNED NOT NULL,
  `post_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `friends`
--

CREATE TABLE `friends` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `friend_id` int(11) UNSIGNED DEFAULT NULL,
  `status` enum('R','A') DEFAULT 'A' COMMENT 'R= request, A=Approve',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `global_fires`
--

CREATE TABLE `global_fires` (
  `id` int(11) NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT '0',
  `fire_days` int(11) DEFAULT '0',
  `streak_days` int(11) DEFAULT '0',
  `last_day` date DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `global_fires`
--

INSERT INTO `global_fires` (`id`, `user_id`, `fire_days`, `streak_days`, `last_day`, `created`, `modified`) VALUES
(1, 1, 0, 0, '2025-07-18', '2024-09-14 19:41:43', '2025-07-18 21:54:16'),
(2, 2, 0, 0, '2025-07-10', '2024-10-09 16:56:25', '2025-07-10 22:24:27'),
(3, 3, 0, 0, '2025-07-10', '2025-07-08 17:54:45', '2025-07-10 16:41:03');

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `grade` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `inflections`
--

CREATE TABLE `inflections` (
  `id` int(11) UNSIGNED NOT NULL,
  `headword` varchar(255) NOT NULL,
  `reference_dictionary_id` int(11) UNSIGNED DEFAULT NULL,
  `inflection_full_entry` text,
  `FSTR_INEXACT` varchar(255) NOT NULL,
  `FSTR_HTML` text NOT NULL,
  `GSTR` varchar(255) NOT NULL,
  `PS` varchar(255) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `learningpaths`
--

CREATE TABLE `learningpaths` (
  `id` int(11) UNSIGNED NOT NULL,
  `label` varchar(255) CHARACTER SET ucs2 DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `admin_access` enum('1','0') COLLATE latin1_general_ci NOT NULL DEFAULT '1',
  `user_access` enum('1','0') COLLATE latin1_general_ci NOT NULL DEFAULT '1',
  `image_id` int(11) UNSIGNED DEFAULT NULL,
  `owner_id` int(11) UNSIGNED DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci ROW_FORMAT=COMPACT;

--
-- Dumping data for table `learningpaths`
--

INSERT INTO `learningpaths` (`id`, `label`, `description`, `admin_access`, `user_access`, `image_id`, `owner_id`, `created`, `modified`) VALUES
(1, 'Mock Path', 'Path with all types of lessons and exercises', '1', '1', NULL, 1, '2024-09-14 21:43:39', '2024-09-14 21:43:39');

-- --------------------------------------------------------

--
-- Table structure for table `learningspeed`
--

CREATE TABLE `learningspeed` (
  `id` int(11) UNSIGNED NOT NULL,
  `label` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `description` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `minutes` int(11) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci ROW_FORMAT=COMPACT;

--
-- Dumping data for table `learningspeed`
--

INSERT INTO `learningspeed` (`id`, `label`, `description`, `minutes`, `created`, `modified`) VALUES
(1, 'Nice and Easy', '7 min./day', 7, NULL, NULL),
(2, 'Regular', '15 min./day', 15, NULL, NULL),
(3, 'Committed', '20 min./day', 20, NULL, NULL),
(4, 'Relentless', '25 min./day', 25, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

CREATE TABLE `lessons` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

--
-- Dumping data for table `lessons`
--

INSERT INTO `lessons` (`id`, `name`, `type`, `created`, `modified`) VALUES
(1, '1 Block, Card, Lakota, English, Audio, Video (1)', NULL, '2024-09-15 10:44:35', '2024-09-16 13:17:23'),
(2, '1 Block, Card, Lakota, English, Audio (1)', NULL, '2024-09-15 13:02:39', '2024-09-16 13:11:36'),
(3, '1 Block, Card, Lakota, English (1)', NULL, '2024-09-15 13:41:23', '2024-09-16 13:08:41'),
(4, '1 Block, Card, Lakota, English, Audio, Video, Image (1)', NULL, '2024-09-15 13:42:52', '2024-09-16 13:19:27'),
(5, '1 Block, Card, Lakota', NULL, '2024-09-15 13:44:29', '2024-09-16 13:03:50'),
(6, '2 Blocks, Card, Lakota (1)', NULL, '2024-09-15 15:42:44', '2024-09-16 13:31:08'),
(7, '2 Blocks, Card, Lakota, English (1)', NULL, '2024-09-15 16:30:54', '2024-09-16 15:44:58'),
(8, '2 Blocks, Card, Lakota, English, Audio (1)', NULL, '2024-09-15 16:33:55', '2024-09-16 15:59:17'),
(9, '2 Blocks, Card, Lakota, English, Audio, Video (1)', NULL, '2024-09-15 16:40:41', '2024-09-16 16:07:26'),
(10, '2 Blocks, Card, Lakota, English, Audio, Video, Image (1)', NULL, '2024-09-15 16:42:06', '2024-09-16 16:10:10'),
(11, '3 Blocks, Card, Lakota (1)', NULL, '2024-09-15 16:46:41', '2024-09-16 16:11:00'),
(12, '3 Blocks, Card, Lakota, English (1)', NULL, '2024-09-15 16:49:02', '2024-09-16 16:23:59'),
(13, '3 Blocks, Card, Lakota, English, Audio (1)', NULL, '2024-09-16 12:24:08', '2024-09-16 16:37:52'),
(14, '3 Blocks, Card, Lakota, English, Audio, Video (1)', NULL, '2024-09-16 12:29:58', '2024-09-16 16:50:55'),
(15, '3 Blocks, Card, Lakota, English, Audio, Video, Image (1)', NULL, '2024-09-16 12:31:55', '2024-09-16 16:54:25'),
(16, '1 Block, Card, English', NULL, '2024-09-16 13:04:10', '2024-09-16 13:04:10'),
(17, '1 Block, Card, Audio', NULL, '2024-09-16 13:04:25', '2024-09-16 13:04:25'),
(18, '1 Block, Card, Video', NULL, '2024-09-16 13:04:35', '2024-09-16 13:04:35'),
(19, '1 Block, Card, Image', NULL, '2024-09-16 13:04:44', '2024-09-16 13:04:44'),
(20, '1 Block, Card, Lakota, Audio (2)', NULL, '2024-09-16 13:06:52', '2024-09-16 13:08:49'),
(21, '1 Block, Card, Lakota, Video (3)', NULL, '2024-09-16 13:07:04', '2024-09-16 13:09:01'),
(22, '1 Block, Card, Lakota, Image (4)', NULL, '2024-09-16 13:07:14', '2024-09-16 13:09:09'),
(23, '1 Block, Card, English, Audio (5)', NULL, '2024-09-16 13:09:26', '2024-09-16 13:09:26'),
(24, '1 Block, Card, English, Video (6)', NULL, '2024-09-16 13:09:47', '2024-09-16 13:09:47'),
(25, '1 Block, Card, English, Image (7)', NULL, '2024-09-16 13:10:02', '2024-09-16 13:10:02'),
(26, '1 Block, Card, Audio, Video (8)', NULL, '2024-09-16 13:10:21', '2024-09-16 13:10:21'),
(27, '1 Block, Card, Audio, Image (9)', NULL, '2024-09-16 13:10:35', '2024-09-16 13:10:35'),
(28, '1 Block, Card, Video, Image (10)', NULL, '2024-09-16 13:10:52', '2024-09-16 13:10:52'),
(29, '1 Block, Card, Lakota, English, Video (2)', NULL, '2024-09-16 13:11:49', '2024-09-16 13:11:49'),
(30, '1 Block, Card, Lakota, English, Image (3)', NULL, '2024-09-16 13:12:00', '2024-09-16 13:12:00'),
(31, '1 Block, Card, Lakota, Audio, Video (4)', NULL, '2024-09-16 13:12:24', '2024-09-16 13:12:24'),
(32, '1 Block, Card, Lakota, Audio, Image (5)', NULL, '2024-09-16 13:12:46', '2024-09-16 13:12:46'),
(33, '1 Block, Card, Lakota, Video, Image (6)', NULL, '2024-09-16 13:13:11', '2024-09-16 13:13:11'),
(34, '1 Block, Card, English, Audio, Video (7)', NULL, '2024-09-16 13:14:06', '2024-09-16 13:14:06'),
(35, '1 Block, Card, English, Audio, Image (8)', NULL, '2024-09-16 13:14:23', '2024-09-16 13:14:23'),
(36, '1 Block, Card, English, Video, Image (9)', NULL, '2024-09-16 13:14:39', '2024-09-16 13:14:39'),
(37, '1 Block, Card, Audio, Video, Image (10)', NULL, '2024-09-16 13:15:03', '2024-09-16 13:15:03'),
(38, '1 Block, Card, Lakota, English, Audio, Image (2)', NULL, '2024-09-16 13:17:35', '2024-09-16 13:17:35'),
(39, '1 Block, Card, Lakota, English, Video, Image (3)', NULL, '2024-09-16 13:17:52', '2024-09-16 13:17:52'),
(40, '1 Block, Card, Lakota, Audio, Video, Image (4)', NULL, '2024-09-16 13:18:10', '2024-09-16 13:18:10'),
(41, '1 Block, Card, English, Audio, Video, Image (5)', NULL, '2024-09-16 13:18:33', '2024-09-16 13:18:33'),
(42, '2 Blocks, Card, English (2)', NULL, '2024-09-16 15:41:56', '2024-09-16 15:41:56'),
(43, '2 Blocks, Card, Audio (3)', NULL, '2024-09-16 15:42:51', '2024-09-16 15:42:51'),
(44, '2 Blocks, Card, Video (4)', NULL, '2024-09-16 15:43:20', '2024-09-16 15:43:20'),
(45, '2 Blocks, Card, Image (5)', NULL, '2024-09-16 15:43:50', '2024-09-16 15:43:50'),
(46, '2 Blocks, Card, Lakota, Audio (2)', NULL, '2024-09-16 15:45:31', '2024-09-16 15:45:31'),
(47, '2 Blocks, Card, Lakota, Video (3)', NULL, '2024-09-16 15:45:57', '2024-09-16 15:45:57'),
(48, '2 Blocks, Card, Lakota, Image (4)', NULL, '2024-09-16 15:46:33', '2024-09-16 15:46:33'),
(49, '2 Blocks, Card, English, Audio (5)', NULL, '2024-09-16 15:47:01', '2024-09-16 15:47:01'),
(50, '2 Blocks, Card, English, Video (6)', NULL, '2024-09-16 15:56:00', '2024-09-16 15:56:00'),
(51, '2 Blocks, Card, English, Image (7)', NULL, '2024-09-16 15:56:18', '2024-09-16 15:56:18'),
(52, '2 Blocks, Card, Audio, Video (8)', NULL, '2024-09-16 15:56:48', '2024-09-16 15:56:48'),
(53, '2 Blocks, Card, Audio, Image (9)', NULL, '2024-09-16 15:57:11', '2024-09-16 15:57:11'),
(54, '2 Blocks, Card, Video, Image (10)', NULL, '2024-09-16 15:57:32', '2024-09-16 15:57:32'),
(55, '2 Blocks, Card, Lakota, English, Video (2)', NULL, '2024-09-16 15:59:37', '2024-09-16 15:59:37'),
(56, '2 Blocks, Card, Lakota, English, Image (3)', NULL, '2024-09-16 16:00:00', '2024-09-16 16:00:00'),
(57, '2 Blocks, Card, Lakota, Audio, Video (4)', NULL, '2024-09-16 16:00:26', '2024-09-16 16:00:26'),
(58, '2 Blocks, Card, Lakota, Audio, Image (5)', NULL, '2024-09-16 16:00:50', '2024-09-16 16:00:50'),
(59, '2 Blocks, Card, Lakota, Video, Image (6)', NULL, '2024-09-16 16:01:17', '2024-09-16 16:01:17'),
(60, '2 Blocks, Card, English, Audio, Video (7)', NULL, '2024-09-16 16:01:48', '2024-09-16 16:01:48'),
(61, '2 Blocks, Card, English, Audio, Image (8)', NULL, '2024-09-16 16:02:11', '2024-09-16 16:02:11'),
(62, '2 Blocks, Card, English, Video, Image (9)', NULL, '2024-09-16 16:02:39', '2024-09-16 16:02:39'),
(63, '2 Blocks, Card, Audio, Video, Image (10)', NULL, '2024-09-16 16:02:59', '2024-09-16 16:02:59'),
(64, '2 Blocks, Card, Lakota, English, Audio, Image (2)', NULL, '2024-09-16 16:07:39', '2024-09-16 16:07:39'),
(65, '2 Blocks, Card, Lakota, English, Video, Image (3)', NULL, '2024-09-16 16:08:04', '2024-09-16 16:08:04'),
(66, '2 Blocks, Card, Lakota, Audio, Video, Image (4)', NULL, '2024-09-16 16:08:34', '2024-09-16 16:08:34'),
(67, '2 Blocks, Card, English, Audio, Video, Image (5)', NULL, '2024-09-16 16:08:58', '2024-09-16 16:08:58'),
(68, '3 Blocks, Card, English (2)', NULL, '2024-09-16 16:21:47', '2024-09-16 16:21:47'),
(69, '3 Blocks, Card, Audio (3)', NULL, '2024-09-16 16:22:11', '2024-09-16 16:22:11'),
(70, '3 Blocks, Card, Video (4)', NULL, '2024-09-16 16:22:34', '2024-09-16 16:22:34'),
(71, '3 Blocks, Card, Image (5)', NULL, '2024-09-16 16:22:56', '2024-09-16 16:22:56'),
(72, '3 Blocks, Card, Lakota, Audio (2)', NULL, '2024-09-16 16:24:28', '2024-09-16 16:24:28'),
(73, '3 Blocks, Card, Lakota, Video (3)', NULL, '2024-09-16 16:24:49', '2024-09-16 16:24:49'),
(74, '3 Blocks, Card, Lakota, Image (4)', NULL, '2024-09-16 16:25:25', '2024-09-16 16:25:25'),
(75, '3 Blocks, Card, English, Audio (5)', NULL, '2024-09-16 16:26:00', '2024-09-16 16:26:00'),
(76, '3 Blocks, Card, English, Video (6)', NULL, '2024-09-16 16:29:31', '2024-09-16 16:29:31'),
(77, '3 Blocks, Card, English, Image (7)', NULL, '2024-09-16 16:30:07', '2024-09-16 16:30:07'),
(78, '3 Blocks, Card, Audio, Video (8)', NULL, '2024-09-16 16:30:56', '2024-09-16 16:30:56'),
(79, '3 Blocks, Card, Audio, Image (9)', NULL, '2024-09-16 16:31:40', '2024-09-16 16:31:40'),
(80, '3 Blocks, Card, Video, Image (10)', NULL, '2024-09-16 16:32:18', '2024-09-16 16:32:18'),
(81, '3 Blocks, Card, Lakota, English, Video (2)', NULL, '2024-09-16 16:37:20', '2024-09-16 16:37:20'),
(82, '3 Blocks, Card, Lakota, English, Image (3)', NULL, '2024-09-16 16:44:54', '2024-09-16 16:44:54'),
(83, '3 Blocks, Card, Lakota, Audio, Video (4)', NULL, '2024-09-16 16:45:30', '2024-09-16 16:45:30'),
(84, '3 Blocks, Card, Lakota, Audio, Image (5)', NULL, '2024-09-16 16:45:58', '2024-09-16 16:45:58'),
(85, '3 Blocks, Card, Lakota, Video, Image (6)', NULL, '2024-09-16 16:46:35', '2024-09-16 16:46:35'),
(86, '3 Blocks, Card, English, Audio, Video (7)', NULL, '2024-09-16 16:47:13', '2024-09-16 16:47:13'),
(87, '3 Blocks, Card, English, Audio, Image (8)', NULL, '2024-09-16 16:47:45', '2024-09-16 16:47:45'),
(88, '3 Blocks, Card, English, Video, Image (9)', NULL, '2024-09-16 16:48:17', '2024-09-16 16:48:17'),
(89, '3 Blocks, Card, Audio, Video, Image (10)', NULL, '2024-09-16 16:48:47', '2024-09-16 16:48:47'),
(90, '3 Blocks, Card, Lakota, English, Audio, Image (2)', NULL, '2024-09-16 16:51:16', '2024-09-16 16:51:16'),
(91, '3 Blocks, Card, Lakota, English, Video, Image (3)', NULL, '2024-09-16 16:51:47', '2024-09-16 16:51:47'),
(92, '3 Blocks, Card, Lakota, Audio, Video, Image (4)', NULL, '2024-09-16 16:52:25', '2024-09-16 16:52:25'),
(93, '3 Blocks, Card, English, Audio, Video, Image (5)', NULL, '2024-09-16 16:52:56', '2024-09-16 16:52:56');

-- --------------------------------------------------------

--
-- Table structure for table `lesson_frames`
--

CREATE TABLE `lesson_frames` (
  `id` int(11) UNSIGNED NOT NULL,
  `lesson_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `audio_id` int(11) UNSIGNED DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `name` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `number_of_block` int(11) DEFAULT '1',
  `frame_preview` enum('landscape','portrait') DEFAULT 'landscape',
  `frameorder` int(11) DEFAULT '1',
  `modified` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

--
-- Dumping data for table `lesson_frames`
--

INSERT INTO `lesson_frames` (`id`, `lesson_id`, `audio_id`, `duration`, `name`, `number_of_block`, `frame_preview`, `frameorder`, `modified`, `created`) VALUES
(5, 1, NULL, NULL, 'Frame 1', 1, 'landscape', 1, '2024-09-16 13:17:06', '2024-09-15 12:46:52'),
(16, 2, NULL, NULL, 'Frame 1', 1, 'landscape', 1, '2024-09-16 13:11:16', '2024-09-15 13:03:03'),
(24, 3, NULL, NULL, 'Frame 1', 1, 'landscape', 1, '2024-09-16 13:06:35', '2024-09-15 13:41:52'),
(28, 4, NULL, NULL, 'Frame 1', 1, 'landscape', 1, '2024-09-15 16:19:27', '2024-09-15 13:43:12'),
(30, 5, NULL, NULL, 'Frame 1', 1, 'landscape', 1, '2024-09-16 13:04:00', '2024-09-15 13:44:30'),
(31, 6, NULL, NULL, 'Frame 1', 2, 'landscape', 1, '2024-09-16 13:31:00', '2024-09-15 15:42:47'),
(49, 7, NULL, NULL, 'Frame 1', 2, 'landscape', 1, '2024-09-16 15:45:21', '2024-09-15 16:30:56'),
(59, 8, NULL, NULL, 'Frame 1', 2, 'landscape', 1, '2024-09-16 15:59:04', '2024-09-15 16:33:57'),
(69, 9, NULL, NULL, 'Frame 1', 2, 'landscape', 1, '2024-09-16 16:07:06', '2024-09-15 16:40:43'),
(74, 10, NULL, NULL, 'Frame 1', 2, 'landscape', 1, '2024-09-16 13:01:56', '2024-09-15 16:42:08'),
(75, 11, NULL, NULL, 'Frame 1', 3, 'landscape', 1, '2024-09-16 16:11:15', '2024-09-15 16:46:42'),
(81, 12, NULL, NULL, 'Frame 1', 3, 'landscape', 1, '2024-09-16 16:24:17', '2024-09-15 16:49:04'),
(91, 13, NULL, NULL, 'Frame 1', 3, 'landscape', 1, '2024-09-16 16:37:11', '2024-09-16 12:25:30'),
(101, 14, NULL, NULL, 'Frame 1', 3, 'landscape', 1, '2024-09-16 16:51:07', '2024-09-16 12:30:02'),
(106, 15, NULL, NULL, 'Frame 1', 3, 'landscape', 1, '2024-09-16 16:54:33', '2024-09-16 12:32:02'),
(107, 16, NULL, NULL, 'Frame 1', 1, 'landscape', 1, '2024-09-16 13:04:18', '2024-09-16 13:04:13'),
(108, 17, NULL, NULL, 'Frame 1', 1, 'landscape', 1, '2025-05-22 17:23:31', '2024-09-16 13:04:26'),
(109, 18, NULL, NULL, 'Frame 1', 1, 'landscape', 1, '2024-09-16 13:04:40', '2024-09-16 13:04:36'),
(110, 19, NULL, NULL, 'Frame 1', 1, 'landscape', 1, '2024-09-16 13:04:51', '2024-09-16 13:04:45'),
(111, 20, NULL, NULL, 'Frame 1', 1, 'landscape', 1, '2024-09-16 13:06:58', '2024-09-16 13:06:53'),
(112, 21, NULL, NULL, 'Frame 1', 1, 'landscape', 1, '2024-09-16 13:07:10', '2024-09-16 13:07:05'),
(113, 22, NULL, NULL, 'Frame 1', 1, 'landscape', 1, '2024-09-16 13:07:39', '2024-09-16 13:07:34'),
(114, 23, NULL, NULL, 'Frame 1', 1, 'landscape', 1, '2024-09-16 13:09:32', '2024-09-16 13:09:27'),
(115, 24, NULL, NULL, 'Frame 1', 1, 'landscape', 1, '2024-09-16 13:09:54', '2024-09-16 13:09:48'),
(116, 25, NULL, NULL, 'Frame 1', 1, 'landscape', 1, '2024-09-16 13:10:08', '2024-09-16 13:10:03'),
(117, 26, NULL, NULL, 'Frame 1', 1, 'landscape', 1, '2024-09-16 13:10:26', '2024-09-16 13:10:22'),
(118, 27, NULL, NULL, 'Frame 1', 1, 'landscape', 1, '2024-09-16 13:10:41', '2024-09-16 13:10:35'),
(119, 28, NULL, NULL, 'Frame 1', 1, 'landscape', 1, '2024-09-16 13:10:57', '2024-09-16 13:10:53'),
(120, 29, NULL, NULL, 'Frame 1', 1, 'landscape', 1, '2024-09-16 13:11:54', '2024-09-16 13:11:50'),
(121, 30, NULL, NULL, 'Frame 1', 1, 'landscape', 1, '2024-09-16 13:12:09', '2024-09-16 13:12:01'),
(122, 31, NULL, NULL, 'Frame 1', 1, 'landscape', 1, '2024-09-16 13:12:30', '2024-09-16 13:12:24'),
(123, 32, NULL, NULL, 'Frame 1', 1, 'landscape', 1, '2024-09-16 13:12:58', '2024-09-16 13:12:48'),
(124, 33, NULL, NULL, 'Frame 1', 1, 'landscape', 1, '2024-09-16 13:13:24', '2024-09-16 13:13:12'),
(125, 34, NULL, NULL, 'Frame 1', 1, 'landscape', 1, '2024-09-16 13:14:11', '2024-09-16 13:14:07'),
(126, 35, NULL, NULL, 'Frame 1', 1, 'landscape', 1, '2024-09-16 13:14:28', '2024-09-16 13:14:24'),
(127, 36, NULL, NULL, 'Frame 1', 1, 'landscape', 1, '2025-05-22 16:27:53', '2024-09-16 13:14:41'),
(128, 37, NULL, NULL, 'Frame 1', 1, 'landscape', 1, '2024-09-16 13:15:12', '2024-09-16 13:15:05'),
(129, 38, NULL, NULL, 'Frame 1', 1, 'landscape', 1, '2024-09-16 13:17:42', '2024-09-16 13:17:36'),
(130, 39, NULL, NULL, 'Frame 1', 1, 'landscape', 1, '2024-09-16 13:17:58', '2024-09-16 13:17:54'),
(131, 40, NULL, NULL, 'Frame 1', 1, 'landscape', 1, '2024-09-16 13:18:17', '2024-09-16 13:18:12'),
(132, 41, NULL, NULL, 'Frame 1', 1, 'landscape', 1, '2024-09-16 13:18:39', '2024-09-16 13:18:34'),
(133, 42, NULL, NULL, 'Frame 1', 2, 'landscape', 1, '2024-09-16 15:42:41', '2024-09-16 15:42:10'),
(134, 43, NULL, NULL, 'Frame 1', 2, 'landscape', 1, '2024-09-16 15:43:08', '2024-09-16 15:42:58'),
(135, 44, NULL, NULL, 'Frame 1', 2, 'landscape', 1, '2024-09-16 15:43:30', '2024-09-16 15:43:22'),
(136, 45, NULL, NULL, 'Frame 1', 2, 'landscape', 1, '2024-09-16 15:43:59', '2024-09-16 15:43:51'),
(137, 46, NULL, NULL, 'Frame 1', 2, 'landscape', 1, '2024-09-16 15:45:42', '2024-09-16 15:45:32'),
(138, 47, NULL, NULL, 'Frame 1', 2, 'landscape', 1, '2024-09-16 15:46:22', '2024-09-16 15:45:58'),
(139, 48, NULL, NULL, 'Frame 1', 2, 'landscape', 1, '2024-09-16 15:46:44', '2024-09-16 15:46:34'),
(140, 49, NULL, NULL, 'Frame 1', 2, 'landscape', 1, '2024-09-16 15:55:32', '2024-09-16 15:55:08'),
(141, 50, NULL, NULL, 'Frame 1', 2, 'landscape', 1, '2024-09-16 15:56:10', '2024-09-16 15:56:01'),
(142, 51, NULL, NULL, 'Frame 1', 2, 'landscape', 1, '2024-09-16 15:56:32', '2024-09-16 15:56:19'),
(143, 52, NULL, NULL, 'Frame 1', 2, 'landscape', 1, '2024-09-16 15:57:01', '2024-09-16 15:56:50'),
(144, 53, NULL, NULL, 'Frame 1', 2, 'landscape', 1, '2024-09-16 15:57:23', '2024-09-16 15:57:12'),
(145, 54, NULL, NULL, 'Frame 1', 2, 'landscape', 1, '2024-09-16 15:57:43', '2024-09-16 15:57:33'),
(146, 55, NULL, NULL, 'Frame 1', 2, 'landscape', 1, '2024-09-16 15:59:50', '2024-09-16 15:59:39'),
(147, 56, NULL, NULL, 'Frame 1', 2, 'landscape', 1, '2024-09-16 16:00:13', '2024-09-16 16:00:02'),
(148, 57, NULL, NULL, 'Frame 1', 2, 'landscape', 1, '2024-09-16 16:00:41', '2024-09-16 16:00:28'),
(149, 58, NULL, NULL, 'Frame 1', 2, 'landscape', 1, '2024-09-16 16:01:03', '2024-09-16 16:00:52'),
(150, 59, NULL, NULL, 'Frame 1', 2, 'landscape', 1, '2024-09-16 16:01:32', '2024-09-16 16:01:19'),
(151, 60, NULL, NULL, 'Frame 1', 2, 'landscape', 1, '2024-09-16 16:02:00', '2024-09-16 16:01:49'),
(152, 61, NULL, NULL, 'Frame 1', 2, 'landscape', 1, '2024-09-16 16:02:23', '2024-09-16 16:02:12'),
(153, 62, NULL, NULL, 'Frame 1', 2, 'landscape', 1, '2024-09-16 16:02:50', '2024-09-16 16:02:40'),
(154, 63, NULL, NULL, 'Frame 1', 2, 'landscape', 1, '2024-09-16 16:03:13', '2024-09-16 16:03:02'),
(155, 64, NULL, NULL, 'Frame 1', 2, 'landscape', 1, '2024-09-16 16:07:51', '2024-09-16 16:07:40'),
(156, 65, NULL, NULL, 'Frame 1', 2, 'landscape', 1, '2024-09-16 16:08:16', '2024-09-16 16:08:05'),
(157, 66, NULL, NULL, 'Frame 1', 2, 'landscape', 1, '2024-09-16 16:08:47', '2024-09-16 16:08:36'),
(158, 67, NULL, NULL, 'Frame 1', 2, 'landscape', 1, '2024-09-16 16:09:13', '2024-09-16 16:09:00'),
(159, 68, NULL, NULL, 'Frame 1', 3, 'landscape', 1, '2024-09-16 16:22:04', '2024-09-16 16:21:49'),
(160, 69, NULL, NULL, 'Frame 1', 3, 'landscape', 1, '2024-09-16 16:22:27', '2024-09-16 16:22:12'),
(161, 70, NULL, NULL, 'Frame 1', 3, 'landscape', 1, '2024-09-16 16:22:49', '2024-09-16 16:22:35'),
(162, 71, NULL, NULL, 'Frame 1', 3, 'landscape', 1, '2024-09-16 16:23:09', '2024-09-16 16:22:56'),
(163, 72, NULL, NULL, 'Frame 1', 3, 'landscape', 1, '2024-09-16 16:24:43', '2024-09-16 16:24:29'),
(164, 73, NULL, NULL, 'Frame 1', 3, 'landscape', 1, '2024-09-16 16:25:14', '2024-09-16 16:24:50'),
(165, 74, NULL, NULL, 'Frame 1', 3, 'landscape', 1, '2024-09-16 16:25:44', '2024-09-16 16:25:26'),
(166, 75, NULL, NULL, 'Frame 1', 3, 'landscape', 1, '2024-09-16 16:26:15', '2024-09-16 16:26:01'),
(167, 76, NULL, NULL, 'Frame 1', 3, 'landscape', 1, '2024-09-16 16:29:58', '2024-09-16 16:29:33'),
(168, 77, NULL, NULL, 'Frame 1', 3, 'landscape', 1, '2024-09-16 16:30:31', '2024-09-16 16:30:08'),
(169, 78, NULL, NULL, 'Frame 1', 3, 'landscape', 1, '2024-09-16 16:31:29', '2024-09-16 16:30:58'),
(170, 79, NULL, NULL, 'Frame 1', 3, 'landscape', 1, '2024-09-16 16:32:05', '2024-09-16 16:31:41'),
(171, 80, NULL, NULL, 'Frame 1', 3, 'landscape', 1, '2024-09-16 16:33:07', '2024-09-16 16:32:20'),
(172, 81, NULL, NULL, 'Frame 1', 3, 'landscape', 1, '2024-09-16 16:37:38', '2024-09-16 16:37:22'),
(173, 82, NULL, NULL, 'Frame 1', 3, 'landscape', 1, '2024-09-16 16:45:14', '2024-09-16 16:44:55'),
(174, 83, NULL, NULL, 'Frame 1', 3, 'landscape', 1, '2024-09-16 16:45:51', '2024-09-16 16:45:31'),
(175, 84, NULL, NULL, 'Frame 1', 3, 'landscape', 1, '2024-09-16 16:46:22', '2024-09-16 16:45:59'),
(176, 85, NULL, NULL, 'Frame 1', 3, 'landscape', 1, '2024-09-16 16:46:56', '2024-09-16 16:46:36'),
(177, 86, NULL, NULL, 'Frame 1', 3, 'landscape', 1, '2024-09-16 16:47:33', '2024-09-16 16:47:15'),
(178, 87, NULL, NULL, 'Frame 1', 3, 'landscape', 1, '2024-09-16 16:48:04', '2024-09-16 16:47:46'),
(179, 88, NULL, NULL, 'Frame 1', 3, 'landscape', 1, '2024-09-16 16:48:37', '2024-09-16 16:48:18'),
(180, 89, NULL, NULL, 'Frame 1', 3, 'landscape', 1, '2024-09-16 16:49:06', '2024-09-16 16:48:48'),
(181, 90, NULL, NULL, 'Frame 1', 3, 'landscape', 1, '2024-09-16 16:51:35', '2024-09-16 16:51:18'),
(182, 91, NULL, NULL, 'Frame 1', 3, 'landscape', 1, '2024-09-16 16:52:12', '2024-09-16 16:51:50'),
(183, 92, NULL, NULL, 'Frame 1', 3, 'landscape', 1, '2024-09-16 16:52:44', '2024-09-16 16:52:27'),
(184, 93, NULL, NULL, 'Frame 1', 3, 'landscape', 1, '2024-09-16 16:53:15', '2024-09-16 16:52:57'),
(187, 17, NULL, NULL, 'Frame 2', 1, 'landscape', 2, '2025-05-22 17:34:06', '2025-05-22 17:34:06');

-- --------------------------------------------------------

--
-- Table structure for table `lesson_frame_blocks`
--

CREATE TABLE `lesson_frame_blocks` (
  `id` int(11) UNSIGNED NOT NULL,
  `lesson_frame_id` int(11) UNSIGNED DEFAULT NULL,
  `card_id` int(11) UNSIGNED DEFAULT NULL,
  `audio_id` int(11) UNSIGNED DEFAULT NULL,
  `image_id` int(11) UNSIGNED DEFAULT NULL,
  `video_id` int(11) UNSIGNED DEFAULT NULL,
  `block_no` int(11) DEFAULT NULL,
  `type` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL COMMENT 'card,custom HTML,Audio file,Image file,Video file',
  `is_card_lakota` enum('Y','N') DEFAULT 'N',
  `is_card_english` enum('Y','N') DEFAULT 'N',
  `is_card_audio` enum('Y','N') DEFAULT 'N',
  `is_card_video` enum('Y','N') DEFAULT 'N',
  `is_card_image` enum('Y','N') DEFAULT 'N',
  `custom_html` text,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

--
-- Dumping data for table `lesson_frame_blocks`
--

INSERT INTO `lesson_frame_blocks` (`id`, `lesson_frame_id`, `card_id`, `audio_id`, `image_id`, `video_id`, `block_no`, `type`, `is_card_lakota`, `is_card_english`, `is_card_audio`, `is_card_video`, `is_card_image`, `custom_html`, `created`, `modified`) VALUES
(55, 30, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'N', 'N', 'N', 'N', NULL, '2024-09-15 15:53:45', '2024-09-15 15:53:45'),
(60, 24, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'Y', 'N', 'N', 'N', NULL, '2024-09-15 16:13:51', '2024-09-15 16:13:51'),
(71, 16, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'Y', 'Y', 'N', 'N', NULL, '2024-09-15 16:15:58', '2024-09-15 16:15:58'),
(82, 5, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'Y', 'Y', 'Y', 'N', NULL, '2024-09-15 16:18:08', '2024-09-15 16:18:08'),
(87, 28, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'Y', 'Y', 'Y', 'Y', NULL, '2024-09-15 16:19:25', '2024-09-15 16:19:25'),
(251, 31, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'N', 'N', 'N', 'N', NULL, '2024-09-16 12:59:10', '2024-09-16 12:59:10'),
(252, 31, 2, NULL, NULL, NULL, 2, 'card', 'Y', 'N', 'N', 'N', 'N', NULL, '2024-09-16 12:59:10', '2024-09-16 12:59:10'),
(281, 59, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'Y', 'Y', 'N', 'N', NULL, '2024-09-16 13:00:28', '2024-09-16 13:00:28'),
(282, 59, 2, NULL, NULL, NULL, 2, 'card', 'Y', 'Y', 'Y', 'N', 'N', NULL, '2024-09-16 13:00:28', '2024-09-16 13:00:28'),
(311, 74, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'Y', 'Y', 'Y', 'Y', NULL, '2024-09-16 13:01:56', '2024-09-16 13:01:56'),
(312, 74, 2, NULL, NULL, NULL, 2, 'card', 'Y', 'Y', 'Y', 'Y', 'Y', NULL, '2024-09-16 13:01:56', '2024-09-16 13:01:56'),
(313, 107, 1, NULL, NULL, NULL, 1, 'card', 'N', 'Y', 'N', 'N', 'N', NULL, '2024-09-16 13:04:18', '2024-09-16 13:04:18'),
(314, 108, 1, NULL, NULL, NULL, 1, 'card', 'N', 'N', 'Y', 'N', 'N', NULL, '2024-09-16 13:04:30', '2024-09-16 13:04:30'),
(315, 109, 1, NULL, NULL, NULL, 1, 'card', 'N', 'N', 'N', 'Y', 'N', NULL, '2024-09-16 13:04:40', '2024-09-16 13:04:40'),
(316, 110, 1, NULL, NULL, NULL, 1, 'card', 'N', 'N', 'N', 'N', 'Y', NULL, '2024-09-16 13:04:51', '2024-09-16 13:04:51'),
(317, 111, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'N', 'Y', 'N', 'N', NULL, '2024-09-16 13:06:58', '2024-09-16 13:06:58'),
(318, 112, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'N', 'N', 'Y', 'N', NULL, '2024-09-16 13:07:10', '2024-09-16 13:07:10'),
(319, 113, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'N', 'N', 'N', 'Y', NULL, '2024-09-16 13:07:39', '2024-09-16 13:07:39'),
(320, 114, 1, NULL, NULL, NULL, 1, 'card', 'N', 'Y', 'Y', 'N', 'N', NULL, '2024-09-16 13:09:32', '2024-09-16 13:09:32'),
(321, 115, 1, NULL, NULL, NULL, 1, 'card', 'N', 'Y', 'N', 'Y', 'N', NULL, '2024-09-16 13:09:54', '2024-09-16 13:09:54'),
(322, 116, 1, NULL, NULL, NULL, 1, 'card', 'N', 'Y', 'N', 'N', 'Y', NULL, '2024-09-16 13:10:08', '2024-09-16 13:10:08'),
(323, 117, 1, NULL, NULL, NULL, 1, 'card', 'N', 'N', 'Y', 'Y', 'N', NULL, '2024-09-16 13:10:26', '2024-09-16 13:10:26'),
(324, 118, 1, NULL, NULL, NULL, 1, 'card', 'N', 'N', 'Y', 'N', 'Y', NULL, '2024-09-16 13:10:41', '2024-09-16 13:10:41'),
(325, 119, 1, NULL, NULL, NULL, 1, 'card', 'N', 'N', 'N', 'Y', 'Y', NULL, '2024-09-16 13:10:57', '2024-09-16 13:10:57'),
(326, 120, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'Y', 'N', 'Y', 'N', NULL, '2024-09-16 13:11:54', '2024-09-16 13:11:54'),
(327, 121, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'Y', 'N', 'N', 'Y', NULL, '2024-09-16 13:12:10', '2024-09-16 13:12:10'),
(328, 122, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'N', 'Y', 'Y', 'N', NULL, '2024-09-16 13:12:30', '2024-09-16 13:12:30'),
(329, 123, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'N', 'Y', 'N', 'Y', NULL, '2024-09-16 13:12:58', '2024-09-16 13:12:58'),
(330, 124, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'N', 'N', 'Y', 'Y', NULL, '2024-09-16 13:13:24', '2024-09-16 13:13:24'),
(331, 125, 1, NULL, NULL, NULL, 1, 'card', 'N', 'Y', 'Y', 'Y', 'N', NULL, '2024-09-16 13:14:11', '2024-09-16 13:14:11'),
(332, 126, 1, NULL, NULL, NULL, 1, 'card', 'N', 'Y', 'Y', 'N', 'Y', NULL, '2024-09-16 13:14:28', '2024-09-16 13:14:28'),
(333, 127, 1, NULL, NULL, NULL, 1, 'card', 'N', 'Y', 'N', 'Y', 'Y', NULL, '2024-09-16 13:14:53', '2024-09-16 13:14:53'),
(334, 128, 1, NULL, NULL, NULL, 1, 'card', 'N', 'N', 'Y', 'Y', 'Y', NULL, '2024-09-16 13:15:12', '2024-09-16 13:15:12'),
(335, 129, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'Y', 'Y', 'N', 'Y', NULL, '2024-09-16 13:17:42', '2024-09-16 13:17:42'),
(336, 130, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'Y', 'N', 'Y', 'Y', NULL, '2024-09-16 13:17:58', '2024-09-16 13:17:58'),
(337, 131, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'N', 'Y', 'Y', 'Y', NULL, '2024-09-16 13:18:17', '2024-09-16 13:18:17'),
(338, 132, 1, NULL, NULL, NULL, 1, 'card', 'N', 'Y', 'Y', 'Y', 'Y', NULL, '2024-09-16 13:18:39', '2024-09-16 13:18:39'),
(339, 133, 1, NULL, NULL, NULL, 1, 'card', 'N', 'Y', 'N', 'N', 'N', NULL, '2024-09-16 15:42:41', '2024-09-16 15:42:41'),
(340, 133, 1, NULL, NULL, NULL, 2, 'card', 'N', 'Y', 'N', 'N', 'N', NULL, '2024-09-16 15:42:41', '2024-09-16 15:42:41'),
(341, 134, 1, NULL, NULL, NULL, 1, 'card', 'N', 'N', 'Y', 'N', 'N', NULL, '2024-09-16 15:43:08', '2024-09-16 15:43:08'),
(342, 134, 1, NULL, NULL, NULL, 2, 'card', 'N', 'N', 'Y', 'N', 'N', NULL, '2024-09-16 15:43:08', '2024-09-16 15:43:08'),
(343, 135, 1, NULL, NULL, NULL, 1, 'card', 'N', 'N', 'N', 'Y', 'N', NULL, '2024-09-16 15:43:30', '2024-09-16 15:43:30'),
(344, 135, 1, NULL, NULL, NULL, 2, 'card', 'N', 'N', 'N', 'Y', 'N', NULL, '2024-09-16 15:43:30', '2024-09-16 15:43:30'),
(345, 136, 1, NULL, NULL, NULL, 1, 'card', 'N', 'N', 'N', 'N', 'Y', NULL, '2024-09-16 15:43:59', '2024-09-16 15:43:59'),
(346, 136, 1, NULL, NULL, NULL, 2, 'card', 'N', 'N', 'N', 'N', 'Y', NULL, '2024-09-16 15:43:59', '2024-09-16 15:43:59'),
(347, 49, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'Y', 'N', 'N', 'N', NULL, '2024-09-16 15:45:21', '2024-09-16 15:45:21'),
(348, 49, 2, NULL, NULL, NULL, 2, 'card', 'Y', 'Y', 'N', 'N', 'N', NULL, '2024-09-16 15:45:21', '2024-09-16 15:45:21'),
(349, 137, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'N', 'Y', 'N', 'N', NULL, '2024-09-16 15:45:42', '2024-09-16 15:45:42'),
(350, 137, 2, NULL, NULL, NULL, 2, 'card', 'Y', 'N', 'Y', 'N', 'N', NULL, '2024-09-16 15:45:42', '2024-09-16 15:45:42'),
(351, 138, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'N', 'N', 'Y', 'N', NULL, '2024-09-16 15:46:22', '2024-09-16 15:46:22'),
(352, 138, 2, NULL, NULL, NULL, 2, 'card', 'Y', 'N', 'N', 'Y', 'N', NULL, '2024-09-16 15:46:22', '2024-09-16 15:46:22'),
(353, 139, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'N', 'N', 'N', 'Y', NULL, '2024-09-16 15:46:44', '2024-09-16 15:46:44'),
(354, 139, 2, NULL, NULL, NULL, 2, 'card', 'Y', 'N', 'N', 'N', 'Y', NULL, '2024-09-16 15:46:44', '2024-09-16 15:46:44'),
(357, 140, 1, NULL, NULL, NULL, 1, 'card', 'N', 'Y', 'Y', 'N', 'N', NULL, '2024-09-16 15:55:32', '2024-09-16 15:55:32'),
(358, 140, 2, NULL, NULL, NULL, 2, 'card', 'N', 'Y', 'Y', 'N', 'N', NULL, '2024-09-16 15:55:32', '2024-09-16 15:55:32'),
(359, 141, 1, NULL, NULL, NULL, 1, 'card', 'N', 'Y', 'N', 'Y', 'N', NULL, '2024-09-16 15:56:10', '2024-09-16 15:56:10'),
(360, 141, 2, NULL, NULL, NULL, 2, 'card', 'N', 'Y', 'N', 'Y', 'N', NULL, '2024-09-16 15:56:10', '2024-09-16 15:56:10'),
(361, 142, 1, NULL, NULL, NULL, 1, 'card', 'N', 'Y', 'N', 'N', 'Y', NULL, '2024-09-16 15:56:32', '2024-09-16 15:56:32'),
(362, 142, 2, NULL, NULL, NULL, 2, 'card', 'N', 'Y', 'N', 'N', 'Y', NULL, '2024-09-16 15:56:32', '2024-09-16 15:56:32'),
(363, 143, 1, NULL, NULL, NULL, 1, 'card', 'N', 'N', 'Y', 'Y', 'N', NULL, '2024-09-16 15:57:01', '2024-09-16 15:57:01'),
(364, 143, 2, NULL, NULL, NULL, 2, 'card', 'N', 'N', 'Y', 'Y', 'N', NULL, '2024-09-16 15:57:01', '2024-09-16 15:57:01'),
(365, 144, 1, NULL, NULL, NULL, 1, 'card', 'N', 'N', 'Y', 'N', 'Y', NULL, '2024-09-16 15:57:23', '2024-09-16 15:57:23'),
(366, 144, 2, NULL, NULL, NULL, 2, 'card', 'N', 'N', 'Y', 'N', 'Y', NULL, '2024-09-16 15:57:23', '2024-09-16 15:57:23'),
(367, 145, 1, NULL, NULL, NULL, 1, 'card', 'N', 'N', 'N', 'Y', 'Y', NULL, '2024-09-16 15:57:43', '2024-09-16 15:57:43'),
(368, 145, 2, NULL, NULL, NULL, 2, 'card', 'N', 'N', 'N', 'Y', 'Y', NULL, '2024-09-16 15:57:43', '2024-09-16 15:57:43'),
(369, 146, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'Y', 'N', 'Y', 'N', NULL, '2024-09-16 15:59:50', '2024-09-16 15:59:50'),
(370, 146, 2, NULL, NULL, NULL, 2, 'card', 'Y', 'Y', 'N', 'Y', 'N', NULL, '2024-09-16 15:59:50', '2024-09-16 15:59:50'),
(371, 147, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'Y', 'N', 'N', 'Y', NULL, '2024-09-16 16:00:13', '2024-09-16 16:00:13'),
(372, 147, 2, NULL, NULL, NULL, 2, 'card', 'Y', 'Y', 'N', 'N', 'Y', NULL, '2024-09-16 16:00:13', '2024-09-16 16:00:13'),
(373, 148, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'N', 'Y', 'Y', 'N', NULL, '2024-09-16 16:00:41', '2024-09-16 16:00:41'),
(374, 148, 2, NULL, NULL, NULL, 2, 'card', 'Y', 'N', 'Y', 'Y', 'N', NULL, '2024-09-16 16:00:41', '2024-09-16 16:00:41'),
(375, 149, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'N', 'Y', 'N', 'Y', NULL, '2024-09-16 16:01:03', '2024-09-16 16:01:03'),
(376, 149, 2, NULL, NULL, NULL, 2, 'card', 'Y', 'N', 'Y', 'N', 'Y', NULL, '2024-09-16 16:01:03', '2024-09-16 16:01:03'),
(377, 150, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'N', 'N', 'Y', 'Y', NULL, '2024-09-16 16:01:32', '2024-09-16 16:01:32'),
(378, 150, 2, NULL, NULL, NULL, 2, 'card', 'Y', 'N', 'N', 'Y', 'Y', NULL, '2024-09-16 16:01:32', '2024-09-16 16:01:32'),
(379, 151, 1, NULL, NULL, NULL, 1, 'card', 'N', 'Y', 'Y', 'Y', 'N', NULL, '2024-09-16 16:02:00', '2024-09-16 16:02:00'),
(380, 151, 2, NULL, NULL, NULL, 2, 'card', 'N', 'Y', 'Y', 'Y', 'N', NULL, '2024-09-16 16:02:00', '2024-09-16 16:02:00'),
(381, 152, 1, NULL, NULL, NULL, 1, 'card', 'N', 'Y', 'Y', 'N', 'Y', NULL, '2024-09-16 16:02:23', '2024-09-16 16:02:23'),
(382, 152, 2, NULL, NULL, NULL, 2, 'card', 'N', 'Y', 'Y', 'N', 'Y', NULL, '2024-09-16 16:02:23', '2024-09-16 16:02:23'),
(383, 153, 1, NULL, NULL, NULL, 1, 'card', 'N', 'Y', 'N', 'Y', 'Y', NULL, '2024-09-16 16:02:50', '2024-09-16 16:02:50'),
(384, 153, 2, NULL, NULL, NULL, 2, 'card', 'N', 'Y', 'N', 'Y', 'Y', NULL, '2024-09-16 16:02:50', '2024-09-16 16:02:50'),
(385, 154, 1, NULL, NULL, NULL, 1, 'card', 'N', 'N', 'Y', 'Y', 'Y', NULL, '2024-09-16 16:03:13', '2024-09-16 16:03:13'),
(386, 154, 2, NULL, NULL, NULL, 2, 'card', 'N', 'N', 'Y', 'Y', 'Y', NULL, '2024-09-16 16:03:13', '2024-09-16 16:03:13'),
(387, 69, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'Y', 'Y', 'Y', 'N', NULL, '2024-09-16 16:07:06', '2024-09-16 16:07:06'),
(388, 69, 2, NULL, NULL, NULL, 2, 'card', 'Y', 'Y', 'Y', 'Y', 'N', NULL, '2024-09-16 16:07:06', '2024-09-16 16:07:06'),
(389, 155, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'Y', 'Y', 'N', 'Y', NULL, '2024-09-16 16:07:51', '2024-09-16 16:07:51'),
(390, 155, 2, NULL, NULL, NULL, 2, 'card', 'Y', 'Y', 'Y', 'N', 'Y', NULL, '2024-09-16 16:07:51', '2024-09-16 16:07:51'),
(391, 156, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'Y', 'N', 'Y', 'Y', NULL, '2024-09-16 16:08:16', '2024-09-16 16:08:16'),
(392, 156, 2, NULL, NULL, NULL, 2, 'card', 'Y', 'Y', 'N', 'Y', 'Y', NULL, '2024-09-16 16:08:16', '2024-09-16 16:08:16'),
(393, 157, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'N', 'Y', 'Y', 'Y', NULL, '2024-09-16 16:08:47', '2024-09-16 16:08:47'),
(394, 157, 2, NULL, NULL, NULL, 2, 'card', 'Y', 'N', 'Y', 'Y', 'Y', NULL, '2024-09-16 16:08:47', '2024-09-16 16:08:47'),
(395, 158, 1, NULL, NULL, NULL, 1, 'card', 'N', 'Y', 'Y', 'Y', 'Y', NULL, '2024-09-16 16:09:13', '2024-09-16 16:09:13'),
(396, 158, 2, NULL, NULL, NULL, 2, 'card', 'N', 'Y', 'Y', 'Y', 'Y', NULL, '2024-09-16 16:09:14', '2024-09-16 16:09:14'),
(397, 75, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'N', 'N', 'N', 'N', NULL, '2024-09-16 16:11:15', '2024-09-16 16:11:15'),
(398, 75, 2, NULL, NULL, NULL, 2, 'card', 'Y', 'N', 'N', 'N', 'N', NULL, '2024-09-16 16:11:15', '2024-09-16 16:11:15'),
(399, 75, 3, NULL, NULL, NULL, 3, 'card', 'Y', 'N', 'N', 'N', 'N', NULL, '2024-09-16 16:11:15', '2024-09-16 16:11:15'),
(400, 159, 1, NULL, NULL, NULL, 1, 'card', 'N', 'Y', 'N', 'N', 'N', NULL, '2024-09-16 16:22:04', '2024-09-16 16:22:04'),
(401, 159, 2, NULL, NULL, NULL, 2, 'card', 'N', 'Y', 'N', 'N', 'N', NULL, '2024-09-16 16:22:04', '2024-09-16 16:22:04'),
(402, 159, 3, NULL, NULL, NULL, 3, 'card', 'N', 'Y', 'N', 'N', 'N', NULL, '2024-09-16 16:22:04', '2024-09-16 16:22:04'),
(403, 160, 1, NULL, NULL, NULL, 1, 'card', 'N', 'N', 'Y', 'N', 'N', NULL, '2024-09-16 16:22:27', '2024-09-16 16:22:27'),
(404, 160, 2, NULL, NULL, NULL, 2, 'card', 'N', 'N', 'Y', 'N', 'N', NULL, '2024-09-16 16:22:27', '2024-09-16 16:22:27'),
(405, 160, 3, NULL, NULL, NULL, 3, 'card', 'N', 'N', 'Y', 'N', 'N', NULL, '2024-09-16 16:22:27', '2024-09-16 16:22:27'),
(406, 161, 1, NULL, NULL, NULL, 1, 'card', 'N', 'N', 'N', 'Y', 'N', NULL, '2024-09-16 16:22:49', '2024-09-16 16:22:49'),
(407, 161, 2, NULL, NULL, NULL, 2, 'card', 'N', 'N', 'N', 'Y', 'N', NULL, '2024-09-16 16:22:49', '2024-09-16 16:22:49'),
(408, 161, 3, NULL, NULL, NULL, 3, 'card', 'N', 'N', 'N', 'Y', 'N', NULL, '2024-09-16 16:22:49', '2024-09-16 16:22:49'),
(409, 162, 1, NULL, NULL, NULL, 1, 'card', 'N', 'N', 'N', 'N', 'Y', NULL, '2024-09-16 16:23:09', '2024-09-16 16:23:09'),
(410, 162, 2, NULL, NULL, NULL, 2, 'card', 'N', 'N', 'N', 'N', 'Y', NULL, '2024-09-16 16:23:09', '2024-09-16 16:23:09'),
(411, 162, 3, NULL, NULL, NULL, 3, 'card', 'N', 'N', 'N', 'N', 'Y', NULL, '2024-09-16 16:23:09', '2024-09-16 16:23:09'),
(412, 81, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'Y', 'N', 'N', 'N', NULL, '2024-09-16 16:24:17', '2024-09-16 16:24:17'),
(413, 81, 2, NULL, NULL, NULL, 2, 'card', 'Y', 'Y', 'N', 'N', 'N', NULL, '2024-09-16 16:24:17', '2024-09-16 16:24:17'),
(414, 81, 3, NULL, NULL, NULL, 3, 'card', 'Y', 'Y', 'N', 'N', 'N', NULL, '2024-09-16 16:24:17', '2024-09-16 16:24:17'),
(415, 163, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'N', 'Y', 'N', 'N', NULL, '2024-09-16 16:24:43', '2024-09-16 16:24:43'),
(416, 163, 2, NULL, NULL, NULL, 2, 'card', 'Y', 'N', 'Y', 'N', 'N', NULL, '2024-09-16 16:24:43', '2024-09-16 16:24:43'),
(417, 163, 3, NULL, NULL, NULL, 3, 'card', 'Y', 'N', 'Y', 'N', 'N', NULL, '2024-09-16 16:24:43', '2024-09-16 16:24:43'),
(418, 164, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'N', 'N', 'Y', 'N', NULL, '2024-09-16 16:25:14', '2024-09-16 16:25:14'),
(419, 164, 2, NULL, NULL, NULL, 2, 'card', 'Y', 'N', 'N', 'Y', 'N', NULL, '2024-09-16 16:25:14', '2024-09-16 16:25:14'),
(420, 164, 3, NULL, NULL, NULL, 3, 'card', 'Y', 'N', 'N', 'Y', 'N', NULL, '2024-09-16 16:25:14', '2024-09-16 16:25:14'),
(421, 165, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'N', 'N', 'N', 'Y', NULL, '2024-09-16 16:25:44', '2024-09-16 16:25:44'),
(422, 165, 2, NULL, NULL, NULL, 2, 'card', 'Y', 'N', 'N', 'N', 'Y', NULL, '2024-09-16 16:25:44', '2024-09-16 16:25:44'),
(423, 165, 3, NULL, NULL, NULL, 3, 'card', 'Y', 'N', 'N', 'N', 'Y', NULL, '2024-09-16 16:25:44', '2024-09-16 16:25:44'),
(424, 166, 1, NULL, NULL, NULL, 1, 'card', 'N', 'Y', 'Y', 'N', 'N', NULL, '2024-09-16 16:26:15', '2024-09-16 16:26:15'),
(425, 166, 2, NULL, NULL, NULL, 2, 'card', 'N', 'Y', 'Y', 'N', 'N', NULL, '2024-09-16 16:26:15', '2024-09-16 16:26:15'),
(426, 166, 3, NULL, NULL, NULL, 3, 'card', 'N', 'Y', 'Y', 'N', 'N', NULL, '2024-09-16 16:26:15', '2024-09-16 16:26:15'),
(427, 167, 1, NULL, NULL, NULL, 1, 'card', 'N', 'Y', 'N', 'Y', 'N', NULL, '2024-09-16 16:29:58', '2024-09-16 16:29:58'),
(428, 167, 2, NULL, NULL, NULL, 2, 'card', 'N', 'Y', 'N', 'Y', 'N', NULL, '2024-09-16 16:29:58', '2024-09-16 16:29:58'),
(429, 167, 3, NULL, NULL, NULL, 3, 'card', 'N', 'Y', 'N', 'Y', 'N', NULL, '2024-09-16 16:29:58', '2024-09-16 16:29:58'),
(430, 168, 1, NULL, NULL, NULL, 1, 'card', 'N', 'Y', 'N', 'N', 'Y', NULL, '2024-09-16 16:30:31', '2024-09-16 16:30:31'),
(431, 168, 2, NULL, NULL, NULL, 2, 'card', 'N', 'Y', 'N', 'N', 'Y', NULL, '2024-09-16 16:30:31', '2024-09-16 16:30:31'),
(432, 168, 3, NULL, NULL, NULL, 3, 'card', 'N', 'Y', 'N', 'N', 'Y', NULL, '2024-09-16 16:30:31', '2024-09-16 16:30:31'),
(433, 169, 1, NULL, NULL, NULL, 1, 'card', 'N', 'N', 'Y', 'Y', 'N', NULL, '2024-09-16 16:31:29', '2024-09-16 16:31:29'),
(434, 169, 2, NULL, NULL, NULL, 2, 'card', 'N', 'N', 'Y', 'Y', 'N', NULL, '2024-09-16 16:31:29', '2024-09-16 16:31:29'),
(435, 169, 3, NULL, NULL, NULL, 3, 'card', 'N', 'N', 'Y', 'Y', 'N', NULL, '2024-09-16 16:31:29', '2024-09-16 16:31:29'),
(436, 170, 1, NULL, NULL, NULL, 1, 'card', 'N', 'N', 'Y', 'N', 'Y', NULL, '2024-09-16 16:32:05', '2024-09-16 16:32:05'),
(437, 170, 2, NULL, NULL, NULL, 2, 'card', 'N', 'N', 'Y', 'N', 'Y', NULL, '2024-09-16 16:32:05', '2024-09-16 16:32:05'),
(438, 170, 3, NULL, NULL, NULL, 3, 'card', 'N', 'N', 'Y', 'N', 'Y', NULL, '2024-09-16 16:32:05', '2024-09-16 16:32:05'),
(439, 171, 1, NULL, NULL, NULL, 1, 'card', 'N', 'N', 'N', 'Y', 'Y', NULL, '2024-09-16 16:33:07', '2024-09-16 16:33:07'),
(440, 171, 2, NULL, NULL, NULL, 2, 'card', 'N', 'N', 'N', 'Y', 'Y', NULL, '2024-09-16 16:33:07', '2024-09-16 16:33:07'),
(441, 171, 3, NULL, NULL, NULL, 3, 'card', 'N', 'N', 'N', 'Y', 'Y', NULL, '2024-09-16 16:33:07', '2024-09-16 16:33:07'),
(442, 91, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'Y', 'Y', 'N', 'N', NULL, '2024-09-16 16:37:11', '2024-09-16 16:37:11'),
(443, 91, 2, NULL, NULL, NULL, 2, 'card', 'Y', 'Y', 'Y', 'N', 'N', NULL, '2024-09-16 16:37:11', '2024-09-16 16:37:11'),
(444, 91, 3, NULL, NULL, NULL, 3, 'card', 'Y', 'Y', 'Y', 'N', 'N', NULL, '2024-09-16 16:37:11', '2024-09-16 16:37:11'),
(445, 172, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'Y', 'N', 'Y', 'N', NULL, '2024-09-16 16:37:38', '2024-09-16 16:37:38'),
(446, 172, 2, NULL, NULL, NULL, 2, 'card', 'Y', 'Y', 'N', 'Y', 'N', NULL, '2024-09-16 16:37:38', '2024-09-16 16:37:38'),
(447, 172, 3, NULL, NULL, NULL, 3, 'card', 'Y', 'Y', 'N', 'N', 'Y', NULL, '2024-09-16 16:37:38', '2024-09-16 16:37:38'),
(448, 173, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'Y', 'N', 'N', 'Y', NULL, '2024-09-16 16:45:14', '2024-09-16 16:45:14'),
(449, 173, 2, NULL, NULL, NULL, 2, 'card', 'Y', 'Y', 'N', 'N', 'Y', NULL, '2024-09-16 16:45:14', '2024-09-16 16:45:14'),
(450, 173, 3, NULL, NULL, NULL, 3, 'card', 'Y', 'Y', 'N', 'N', 'Y', NULL, '2024-09-16 16:45:14', '2024-09-16 16:45:14'),
(451, 174, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'N', 'Y', 'Y', 'N', NULL, '2024-09-16 16:45:51', '2024-09-16 16:45:51'),
(452, 174, 2, NULL, NULL, NULL, 2, 'card', 'Y', 'N', 'Y', 'Y', 'N', NULL, '2024-09-16 16:45:51', '2024-09-16 16:45:51'),
(453, 174, 3, NULL, NULL, NULL, 3, 'card', 'Y', 'N', 'Y', 'Y', 'N', NULL, '2024-09-16 16:45:51', '2024-09-16 16:45:51'),
(454, 175, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'N', 'Y', 'N', 'Y', NULL, '2024-09-16 16:46:22', '2024-09-16 16:46:22'),
(455, 175, 2, NULL, NULL, NULL, 2, 'card', 'Y', 'N', 'Y', 'N', 'Y', NULL, '2024-09-16 16:46:22', '2024-09-16 16:46:22'),
(456, 175, 3, NULL, NULL, NULL, 3, 'card', 'Y', 'N', 'Y', 'N', 'Y', NULL, '2024-09-16 16:46:22', '2024-09-16 16:46:22'),
(457, 176, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'N', 'N', 'Y', 'Y', NULL, '2024-09-16 16:46:56', '2024-09-16 16:46:56'),
(458, 176, 2, NULL, NULL, NULL, 2, 'card', 'Y', 'N', 'N', 'Y', 'Y', NULL, '2024-09-16 16:46:56', '2024-09-16 16:46:56'),
(459, 176, 3, NULL, NULL, NULL, 3, 'card', 'Y', 'N', 'N', 'Y', 'Y', NULL, '2024-09-16 16:46:56', '2024-09-16 16:46:56'),
(460, 177, 1, NULL, NULL, NULL, 1, 'card', 'N', 'Y', 'Y', 'Y', 'N', NULL, '2024-09-16 16:47:33', '2024-09-16 16:47:33'),
(461, 177, 2, NULL, NULL, NULL, 2, 'card', 'N', 'Y', 'Y', 'Y', 'N', NULL, '2024-09-16 16:47:33', '2024-09-16 16:47:33'),
(462, 177, 3, NULL, NULL, NULL, 3, 'card', 'N', 'Y', 'Y', 'Y', 'N', NULL, '2024-09-16 16:47:33', '2024-09-16 16:47:33'),
(463, 178, 1, NULL, NULL, NULL, 1, 'card', 'N', 'Y', 'Y', 'N', 'Y', NULL, '2024-09-16 16:48:04', '2024-09-16 16:48:04'),
(464, 178, 2, NULL, NULL, NULL, 2, 'card', 'N', 'Y', 'Y', 'N', 'Y', NULL, '2024-09-16 16:48:05', '2024-09-16 16:48:05'),
(465, 178, 1, NULL, NULL, NULL, 3, 'card', 'N', 'Y', 'Y', 'N', 'Y', NULL, '2024-09-16 16:48:05', '2024-09-16 16:48:05'),
(466, 179, 1, NULL, NULL, NULL, 1, 'card', 'N', 'Y', 'N', 'Y', 'Y', NULL, '2024-09-16 16:48:37', '2024-09-16 16:48:37'),
(467, 179, 2, NULL, NULL, NULL, 2, 'card', 'N', 'Y', 'N', 'Y', 'Y', NULL, '2024-09-16 16:48:37', '2024-09-16 16:48:37'),
(468, 179, 3, NULL, NULL, NULL, 3, 'card', 'N', 'Y', 'N', 'Y', 'Y', NULL, '2024-09-16 16:48:37', '2024-09-16 16:48:37'),
(469, 180, 1, NULL, NULL, NULL, 1, 'card', 'N', 'N', 'Y', 'Y', 'Y', NULL, '2024-09-16 16:49:06', '2024-09-16 16:49:06'),
(470, 180, 2, NULL, NULL, NULL, 2, 'card', 'N', 'N', 'Y', 'Y', 'Y', NULL, '2024-09-16 16:49:06', '2024-09-16 16:49:06'),
(471, 180, 3, NULL, NULL, NULL, 3, 'card', 'N', 'N', 'Y', 'Y', 'Y', NULL, '2024-09-16 16:49:06', '2024-09-16 16:49:06'),
(472, 101, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'Y', 'Y', 'Y', 'N', NULL, '2024-09-16 16:51:07', '2024-09-16 16:51:07'),
(473, 101, 2, NULL, NULL, NULL, 2, 'card', 'Y', 'Y', 'Y', 'Y', 'N', NULL, '2024-09-16 16:51:07', '2024-09-16 16:51:07'),
(474, 101, 3, NULL, NULL, NULL, 3, 'card', 'Y', 'Y', 'Y', 'Y', 'N', NULL, '2024-09-16 16:51:07', '2024-09-16 16:51:07'),
(475, 181, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'Y', 'Y', 'N', 'Y', NULL, '2024-09-16 16:51:35', '2024-09-16 16:51:35'),
(476, 181, 2, NULL, NULL, NULL, 2, 'card', 'Y', 'Y', 'Y', 'N', 'Y', NULL, '2024-09-16 16:51:35', '2024-09-16 16:51:35'),
(477, 181, 3, NULL, NULL, NULL, 3, 'card', 'Y', 'Y', 'Y', 'N', 'Y', NULL, '2024-09-16 16:51:35', '2024-09-16 16:51:35'),
(478, 182, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'Y', 'N', 'Y', 'Y', NULL, '2024-09-16 16:52:12', '2024-09-16 16:52:12'),
(479, 182, 2, NULL, NULL, NULL, 2, 'card', 'Y', 'Y', 'N', 'Y', 'Y', NULL, '2024-09-16 16:52:12', '2024-09-16 16:52:12'),
(480, 182, 3, NULL, NULL, NULL, 3, 'card', 'Y', 'Y', 'N', 'Y', 'Y', NULL, '2024-09-16 16:52:12', '2024-09-16 16:52:12'),
(481, 183, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'N', 'Y', 'Y', 'Y', NULL, '2024-09-16 16:52:44', '2024-09-16 16:52:44'),
(482, 183, 2, NULL, NULL, NULL, 2, 'card', 'Y', 'N', 'Y', 'Y', 'Y', NULL, '2024-09-16 16:52:44', '2024-09-16 16:52:44'),
(483, 183, 3, NULL, NULL, NULL, 3, 'card', 'Y', 'N', 'Y', 'Y', 'Y', NULL, '2024-09-16 16:52:44', '2024-09-16 16:52:44'),
(484, 184, 1, NULL, NULL, NULL, 1, 'card', 'N', 'Y', 'Y', 'Y', 'Y', NULL, '2024-09-16 16:53:15', '2024-09-16 16:53:15'),
(485, 184, 2, NULL, NULL, NULL, 2, 'card', 'N', 'Y', 'Y', 'Y', 'Y', NULL, '2024-09-16 16:53:15', '2024-09-16 16:53:15'),
(486, 184, 3, NULL, NULL, NULL, 3, 'card', 'N', 'Y', 'Y', 'Y', 'Y', NULL, '2024-09-16 16:53:15', '2024-09-16 16:53:15'),
(487, 106, 1, NULL, NULL, NULL, 1, 'card', 'Y', 'Y', 'Y', 'Y', 'Y', NULL, '2024-09-16 16:54:33', '2024-09-16 16:54:33'),
(488, 106, 2, NULL, NULL, NULL, 2, 'card', 'Y', 'Y', 'Y', 'Y', 'Y', NULL, '2024-09-16 16:54:33', '2024-09-16 16:54:33'),
(489, 106, 3, NULL, NULL, NULL, 3, 'card', 'Y', 'Y', 'Y', 'Y', 'Y', NULL, '2024-09-16 16:54:33', '2024-09-16 16:54:33');

-- --------------------------------------------------------

--
-- Table structure for table `levels`
--

CREATE TABLE `levels` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `image_id` int(11) UNSIGNED DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `levels`
--

INSERT INTO `levels` (`id`, `name`, `description`, `image_id`, `created`, `modified`) VALUES
(2, '1 Block Card Lessons', '1 Block Card Lessons', 4, '2024-09-15 10:27:14', '2024-09-15 15:35:21'),
(3, 'Multiple Choice', 'Multiple Choice', 5, '2024-09-15 10:29:05', '2024-11-08 19:29:41'),
(4, '2 Block Card Lessons', '2 Block Card Lessons', 5, '2024-09-15 15:36:27', '2024-09-15 15:36:27'),
(5, '3 Block Card Lessons', '3 Block Card Lessons', 5, '2024-09-15 16:46:08', '2024-09-15 16:46:08'),
(6, 'Match The Pair', 'Match The Pair', 17, '2024-09-27 18:40:12', '2024-11-08 19:29:34'),
(7, 'Anagram', 'Anagram', 4, '2024-10-29 16:45:58', '2024-11-08 19:23:53'),
(8, 'True/False', 'True/False', 5, '2024-11-02 16:20:35', '2024-11-08 19:26:55'),
(9, 'Fill-in Typing', 'Fill-in Typing', 4, '2024-11-06 03:57:17', '2024-11-06 03:57:17'),
(10, 'Fill-in Multiple Choice', 'Fill-in Multiple Choice', 5, '2024-11-07 19:50:41', '2024-11-07 19:50:41');

-- --------------------------------------------------------

--
-- Table structure for table `level_units`
--

CREATE TABLE `level_units` (
  `id` int(11) UNSIGNED NOT NULL,
  `learningpath_id` int(11) UNSIGNED NOT NULL,
  `level_id` int(11) UNSIGNED NOT NULL,
  `unit_id` int(11) UNSIGNED NOT NULL,
  `optional` tinyint(3) NOT NULL DEFAULT '0',
  `sequence` smallint(6) NOT NULL DEFAULT '1',
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `level_units`
--

INSERT INTO `level_units` (`id`, `learningpath_id`, `level_id`, `unit_id`, `optional`, `sequence`, `created`, `modified`) VALUES
(1, 1, 2, 1, 0, 1, '2024-09-15 10:27:40', '2024-09-15 10:27:40'),
(2, 1, 2, 2, 0, 2, '2024-09-15 13:51:15', '2024-09-15 13:51:15'),
(3, 1, 2, 3, 0, 3, '2024-09-15 15:24:52', '2024-09-15 15:24:52'),
(4, 1, 2, 4, 0, 4, '2024-09-15 15:25:24', '2024-09-15 15:25:24'),
(5, 1, 2, 5, 0, 5, '2024-09-15 15:25:57', '2024-09-15 15:25:57'),
(6, 1, 4, 6, 0, 1, '2024-09-15 16:42:57', '2024-09-15 16:42:57'),
(7, 1, 4, 7, 0, 2, '2024-09-15 16:43:26', '2024-09-15 16:43:26'),
(8, 1, 4, 8, 0, 3, '2024-09-15 16:43:40', '2024-09-15 16:43:40'),
(9, 1, 4, 9, 0, 4, '2024-09-15 16:43:52', '2024-09-15 16:43:52'),
(10, 1, 4, 10, 0, 5, '2024-09-15 16:44:23', '2024-09-15 16:44:23'),
(11, 1, 5, 11, 0, 1, '2024-09-16 12:32:54', '2025-05-22 22:34:50'),
(12, 1, 5, 12, 0, 2, '2024-09-16 12:33:09', '2025-05-22 22:34:50'),
(13, 1, 5, 13, 0, 3, '2024-09-16 12:33:16', '2025-05-22 22:34:50'),
(14, 1, 5, 14, 0, 4, '2024-09-16 12:33:23', '2025-05-22 22:34:50'),
(15, 1, 5, 15, 0, 5, '2024-09-16 12:33:31', '2025-05-22 22:34:50'),
(16, 1, 3, 16, 0, 1, '2024-09-16 18:06:59', '2024-09-16 18:06:59'),
(17, 1, 3, 17, 0, 2, '2024-09-17 15:39:48', '2024-09-17 15:39:48'),
(18, 1, 3, 18, 0, 3, '2024-09-18 07:30:05', '2024-09-18 07:30:05'),
(19, 1, 3, 19, 0, 4, '2024-09-18 11:10:57', '2024-09-18 11:10:57'),
(20, 1, 6, 20, 0, 2, '2024-09-27 18:40:43', '2024-09-27 19:12:14'),
(21, 1, 6, 21, 0, 1, '2024-09-27 19:12:10', '2024-09-27 19:12:14'),
(22, 1, 7, 22, 0, 1, '2024-10-29 16:49:06', '2024-10-29 16:49:06'),
(23, 1, 7, 23, 0, 2, '2024-10-29 17:01:13', '2024-10-29 17:01:13'),
(24, 1, 8, 24, 0, 1, '2024-11-02 16:20:53', '2024-11-02 16:20:53'),
(25, 1, 8, 25, 0, 2, '2024-11-02 16:21:26', '2024-11-02 16:21:26'),
(26, 1, 8, 26, 0, 3, '2024-11-02 16:22:03', '2024-11-02 16:22:03'),
(27, 1, 8, 27, 0, 4, '2024-11-03 22:16:14', '2024-11-03 22:16:14'),
(28, 1, 8, 28, 0, 5, '2024-11-03 22:16:42', '2024-11-03 22:16:42'),
(29, 1, 9, 29, 0, 1, '2024-11-06 03:57:34', '2024-11-06 03:57:34'),
(30, 1, 10, 30, 0, 1, '2024-11-07 19:51:06', '2024-11-07 19:57:12'),
(32, 1, 9, 32, 0, 2, '2024-11-07 19:52:11', '2024-11-07 19:52:11'),
(33, 1, 7, 33, 0, 3, '2024-11-08 19:24:15', '2024-11-08 19:24:15'),
(34, 1, 7, 34, 0, 4, '2024-11-08 19:24:26', '2024-11-08 19:24:26'),
(35, 1, 3, 35, 0, 5, '2024-11-09 00:38:41', '2024-11-09 00:38:41'),
(36, 1, 6, 36, 0, 3, '2024-11-09 00:57:11', '2024-11-09 00:57:11'),
(37, 1, 3, 37, 0, 6, '2025-05-07 23:03:13', '2025-05-07 23:03:13'),
(38, 1, 6, 38, 0, 4, '2025-05-07 23:16:34', '2025-05-07 23:16:34');

-- --------------------------------------------------------

--
-- Table structure for table `passwordreset`
--

CREATE TABLE `passwordreset` (
  `id` int(11) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `path_levels`
--

CREATE TABLE `path_levels` (
  `id` int(11) UNSIGNED NOT NULL,
  `learningpath_id` int(11) UNSIGNED NOT NULL,
  `level_id` int(11) UNSIGNED NOT NULL,
  `sequence` smallint(6) NOT NULL DEFAULT '1',
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `path_levels`
--

INSERT INTO `path_levels` (`id`, `learningpath_id`, `level_id`, `sequence`, `created`, `modified`) VALUES
(2, 1, 2, 1, '2024-09-15 10:27:14', '2025-04-23 17:44:58'),
(3, 1, 3, 4, '2024-09-15 10:29:05', '2025-04-23 17:44:58'),
(4, 1, 4, 2, '2024-09-15 15:36:27', '2025-04-23 17:44:58'),
(5, 1, 5, 3, '2024-09-15 16:46:08', '2025-04-23 17:44:58'),
(6, 1, 6, 5, '2024-09-27 18:40:12', '2025-04-23 17:44:58'),
(7, 1, 7, 6, '2024-10-29 16:45:58', '2025-04-23 17:44:58'),
(8, 1, 8, 7, '2024-11-02 16:20:35', '2025-04-23 17:44:58'),
(9, 1, 9, 8, '2024-11-06 03:57:17', '2025-04-23 17:44:58'),
(10, 1, 10, 9, '2024-11-07 19:50:41', '2025-04-23 17:44:58');

-- --------------------------------------------------------

--
-- Table structure for table `point_references`
--

CREATE TABLE `point_references` (
  `id` int(11) UNSIGNED NOT NULL,
  `exercise` varchar(50) NOT NULL,
  `exercise_type` varchar(50) DEFAULT NULL,
  `card_type` varchar(50) DEFAULT NULL,
  `prompt_type` varchar(50) NOT NULL,
  `response_type` varchar(50) NOT NULL,
  `instructions` varchar(500) NOT NULL,
  `reading_pts` float NOT NULL,
  `writing_pts` float NOT NULL,
  `speaking_pts` float NOT NULL,
  `listening_pts` float NOT NULL,
  `is_review_included` enum('0','1') NOT NULL DEFAULT '1',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

--
-- Dumping data for table `point_references`
--

INSERT INTO `point_references` (`id`, `exercise`, `exercise_type`, `card_type`, `prompt_type`, `response_type`, `instructions`, `reading_pts`, `writing_pts`, `speaking_pts`, `listening_pts`, `is_review_included`, `created`, `modified`) VALUES
(1, 'match-the-pair', NULL, 'Word', 'a', 'i', 'Match the Lakota audio to the correct picture.', 0, 0, 0, 4, '1', '2018-04-11 08:17:48', '2018-04-11 08:17:48'),
(2, 'match-the-pair', NULL, 'Word', 'a', 'l', 'Match the Lakota audio to the correct Lakota word.', 2, 0, 0, 2, '1', '2018-04-11 08:17:48', '2018-04-11 08:17:48'),
(3, 'match-the-pair', NULL, 'Word', 'a', 'e', 'Match the Lakota audio to the correct English word.', 0, 0, 0, 4, '1', '2018-04-11 08:17:48', '2018-04-11 08:17:48'),
(4, 'match-the-pair', NULL, 'Word', 'i', 'l', 'Match the picture to the correct Lakota word.', 3, 0, 0, 0, '1', '2018-04-11 08:17:48', '2018-04-11 08:17:48'),
(5, 'match-the-pair', NULL, 'Word', 'l', 'i', 'Match the Lakota word to the correct picture.', 4, 0, 0, 0, '1', '2018-04-11 08:17:48', '2018-04-11 08:17:48'),
(6, 'match-the-pair', NULL, 'Word', 'l', 'l', '', 4, 0, 0, 0, '0', '2018-04-11 08:17:48', '2018-04-11 08:17:48'),
(7, 'match-the-pair', NULL, 'Word', 'l', 'e', 'Match the Lakota word to the correct English word.', 4, 0, 0, 0, '1', '2018-04-11 08:17:48', '2018-04-11 08:17:48'),
(8, 'match-the-pair', NULL, 'Word', 'e', 'l', 'Match the English word to the correct Lakota word.', 3, 0, 0, 0, '1', '2018-04-11 08:17:48', '2018-04-11 08:17:48'),
(9, 'match-the-pair', NULL, 'Pattern', 'l', 'e', 'Match the Lakota sentence to the correct English sentence.', 3, 0, 0, 0, '1', '2018-04-11 08:17:48', '2018-04-11 08:17:48'),
(10, 'anagram', NULL, 'Word', 'a', 'l', 'Listen to the Lakota audio and rearrange the letters to make the correct Lakota word.', 0, 5, 0, 2, '1', '2018-04-11 08:17:48', '2018-04-11 08:17:48'),
(11, 'anagram', NULL, 'Word', 'i', 'l', 'Look at the picture and rearrange the letters to make the correct Lakota word.', 0, 7, 0, 0, '1', '2018-04-11 08:17:48', '2018-04-11 08:17:48'),
(12, 'anagram', NULL, 'Word', 'e', 'l', 'Read the English word and rearrange the letters to make the correct Lakota word.', 0, 7, 0, 0, '1', '2018-04-11 08:17:48', '2018-04-11 08:17:48'),
(13, 'fill_in_the_blanks_typing', NULL, 'Word', 'a', 'l', 'Listen to the Lakota audio and fill in the missing letters to spell the correct Lakota word.', 0, 3, 0, 2, '1', '2018-04-11 08:17:48', '2018-04-11 08:17:48'),
(14, 'fill_in_the_blanks_typing', NULL, 'Word', 'i', 'l', 'Look at the picture and fill in the missing letters to spell the correct Lakota word.', 0, 5, 0, 0, '1', '2018-04-11 08:17:48', '2018-04-11 08:17:48'),
(15, 'fill_in_the_blanks_typing', NULL, 'Word', 'l', 'l', '', 3, 5, 0, 0, '0', '2018-04-11 08:17:48', '2018-04-11 08:17:48'),
(16, 'fill_in_the_blanks_typing', NULL, 'Word', 'e', 'l', 'Read the English word and fill in the missing letters to spell the correct Lakota word.', 0, 5, 0, 0, '1', '2018-04-11 08:17:48', '2018-04-11 08:17:48'),
(17, 'fill_in_the_blanks_mcq', NULL, 'Word', 'a', 'l', 'Listen to the Lakota audio and select the missing letters that spell the correct Lakota word.', 0, 2, 0, 2, '1', '2018-04-11 08:17:48', '2018-04-11 08:17:48'),
(18, 'fill_in_the_blanks_mcq', NULL, 'Word', 'i', 'l', 'Look at the picture and select the missing letters that spell the correct Lakota word.', 0, 4, 0, 0, '1', '2018-04-11 08:17:49', '2018-04-11 08:17:49'),
(19, 'fill_in_the_blanks_mcq', NULL, 'Word', 'l', 'l', '', 2, 4, 0, 0, '0', '2018-04-11 08:17:49', '2018-04-11 08:17:49'),
(20, 'fill_in_the_blanks_mcq', NULL, 'Word', 'e', 'l', 'Read the English word and select the missing letters that spell the correct Lakota word.', 0, 4, 0, 0, '1', '2018-04-11 08:17:49', '2018-04-11 08:17:49'),
(21, 'Word Search', NULL, 'Word', 'a', 'l', '', 0, 0, 0, 0, '1', '2018-04-11 08:17:49', '2018-04-11 08:17:49'),
(22, 'Word Search', NULL, 'Word', 'i', 'l', '', 0, 0, 0, 0, '1', '2018-04-11 08:17:49', '2018-04-11 08:17:49'),
(23, 'Word Search', NULL, 'Word', 'l', 'l', '', 0, 0, 0, 0, '1', '2018-04-11 08:17:49', '2018-04-11 08:17:49'),
(24, 'Word Search', NULL, 'Word', 'e', 'l', '', 0, 0, 0, 0, '1', '2018-04-11 08:17:49', '2018-04-11 08:17:49'),
(25, 'Crossword', NULL, 'Word', 'a', 'l', '', 0, 0, 0, 0, '1', '2018-04-11 08:17:49', '2018-04-11 08:17:49'),
(26, 'Crossword', NULL, 'Word', 'i', 'l', '', 0, 0, 0, 0, '1', '2018-04-11 08:17:49', '2018-04-11 08:17:49'),
(27, 'Crossword', NULL, 'Word', 'l', 'l', '', 0, 0, 0, 0, '1', '2018-04-11 08:17:49', '2018-04-11 08:17:49'),
(28, 'Crossword', NULL, 'Word', 'e', 'l', '', 0, 0, 0, 0, '1', '2018-04-11 08:17:49', '2018-04-11 08:17:49'),
(29, 'truefalse', NULL, 'Word', 'a', 'a', '', 0, 0, 0, 3, '0', '2018-04-11 08:17:49', '2018-04-11 08:17:49'),
(30, 'truefalse', NULL, 'Word', 'a', 'i', 'Listen to the Lakota audio and look at the picture. The audio matches the picture. Select true or false.', 0, 0, 0, 3, '1', '2018-04-11 08:17:49', '2018-04-11 08:17:49'),
(31, 'truefalse', NULL, 'Word', 'a', 'l', 'Listen to the Lakota audio and read the Lakota word. The audio matches the Lakota word. Select true or false.', 2, 0, 0, 2, '1', '2018-04-11 08:17:49', '2018-04-11 08:17:49'),
(32, 'truefalse', NULL, 'Word', 'a', 'e', 'Listen to the Lakota audio and read the English word. The Lakota audio matches the English word. Select true or false.', 0, 0, 0, 3, '1', '2018-04-11 08:17:49', '2018-04-11 08:17:49'),
(33, 'truefalse', NULL, 'Word', 'i', 'a', 'Look at the picture and listen to the Lakota audio. The picture matches the audio. Select true or false.', 0, 0, 0, 3, '1', '2018-04-11 08:17:49', '2018-04-11 08:17:49'),
(34, 'truefalse', NULL, 'Word', 'i', 'l', 'Look at the picture and read the Lakota word. The picture matches the Lakota word. Select true or false.', 3, 0, 0, 0, '1', '2018-04-11 08:17:49', '2018-04-11 08:17:49'),
(35, 'truefalse', NULL, 'Word', 'l', 'a', 'Read the Lakota word and listen to the Lakota audio. The Lakota word matches the audio. Select true or false.', 1, 0, 0, 2, '1', '2018-04-11 08:17:49', '2018-04-11 08:17:49'),
(36, 'truefalse', NULL, 'Word', 'l', 'i', 'Read the Lakota word and the picture. The Lakota word matches the picture. Select true or false.', 3, 0, 0, 0, '1', '2018-04-11 08:17:49', '2018-04-11 08:17:49'),
(37, 'truefalse', NULL, 'Word', 'l', 'l', '', 4, 0, 0, 0, '0', '2018-04-11 08:17:49', '2018-04-11 08:17:49'),
(38, 'truefalse', NULL, 'Word', 'l', 'e', 'Read the Lakota word and the English word. The Lakota word matches the English one. Select true or false.', 3, 0, 0, 0, '1', '2018-04-11 08:17:50', '2018-04-11 08:17:50'),
(39, 'truefalse', NULL, 'Word', 'e', 'a', 'Read the English word and listen to the Lakota audio. The English word matches the Lakota audio. Select true or false.', 0, 0, 0, 3, '1', '2018-04-11 08:17:50', '2018-04-11 08:17:50'),
(40, 'truefalse', NULL, 'Word', 'e', 'l', 'Read the English word and the Lakota word. The English word matches the Lakota one. Select true or false.', 3, 0, 0, 0, '1', '2018-04-11 08:17:50', '2018-04-11 08:17:50'),
(41, 'truefalse', NULL, 'Pattern', 'a', 'a', '', 0, 0, 0, 6, '0', '2018-04-11 08:17:50', '2018-04-11 08:17:50'),
(42, 'truefalse', NULL, 'Pattern', 'a', 'i', 'Listen to the Lakota audio and look at the picture. The audio matches the picture. Select true or false.', 0, 0, 0, 5, '1', '2018-04-11 08:17:50', '2018-04-11 08:17:50'),
(43, 'truefalse', NULL, 'Pattern', 'a', 'l', 'Listen to the Lakota audio and read the Lakota sentence. The audio matches the Lakota sentence. Select true or false.', 5, 0, 0, 5, '1', '2018-04-11 08:17:50', '2018-04-11 08:17:50'),
(44, 'truefalse', NULL, 'Pattern', 'a', 'e', 'Listen to the Lakota audio and read the English sentence. The Lakota audio matches the English sentence. Select true or false.', 0, 0, 0, 5, '1', '2018-04-11 08:17:50', '2018-04-11 08:17:50'),
(45, 'truefalse', NULL, 'Pattern', 'i', 'a', 'Look at the picture and listen to the Lakota audio. The picture matches the audio. Select true or false.', 0, 0, 0, 6, '1', '2018-04-11 08:17:50', '2018-04-11 08:17:50'),
(46, 'truefalse', NULL, 'Pattern', 'i', 'l', 'Look at the picture and read the Lakota sentence. The picture matches the Lakota sentence. Select true or false.', 6, 0, 0, 0, '1', '2018-04-11 08:17:50', '2018-04-11 08:17:50'),
(47, 'truefalse', NULL, 'Pattern', 'l', 'a', 'Read the Lakota sentence and listen to the Lakota audio. The Lakota sentence matches the audio. Select true or false.', 1, 0, 0, 5, '1', '2018-04-11 08:17:50', '2018-04-11 08:17:50'),
(48, 'truefalse', NULL, 'Pattern', 'l', 'i', 'Read the Lakota sentence and look at the picture. The Lakota sentence matches the picture. Select true or false.', 6, 0, 0, 0, '1', '2018-04-11 08:17:50', '2018-04-11 08:17:50'),
(49, 'truefalse', NULL, 'Pattern', 'l', 'l', '', 7, 0, 0, 0, '0', '2018-04-11 08:17:50', '2018-04-11 08:17:50'),
(50, 'truefalse', NULL, 'Pattern', 'l', 'e', 'Read the Lakota sentence and the English sentence. The Lakota sentence matches the English one. Select true or false.', 6, 0, 0, 0, '1', '2018-04-11 08:17:50', '2018-04-11 08:17:50'),
(51, 'truefalse', NULL, 'Pattern', 'e', 'a', 'Read the English sentence and listen to the Lakota audio. The English sentence matches the Lakota audio. Select true or false.', 0, 0, 0, 6, '1', '2018-04-11 08:17:50', '2018-04-11 08:17:50'),
(52, 'truefalse', NULL, 'Pattern', 'e', 'l', 'Read the English sentence and the Lakota sentence. The English sentence matches the Lakota one. Select true or false.', 6, 0, 0, 0, '1', '2018-04-11 08:17:50', '2018-04-11 08:17:50'),
(53, 'multiple-choice', NULL, 'Word', 'a', 'a', '', 0, 0, 0, 2, '0', '2018-04-11 08:17:50', '2018-04-11 08:17:50'),
(54, 'multiple-choice', NULL, 'Word', 'a', 'i', 'Listen to the Lakota audio and select the correct picture.', 0, 0, 0, 3, '1', '2018-04-11 08:17:50', '2018-04-11 08:17:50'),
(55, 'multiple-choice', NULL, 'Word', 'a', 'l', 'Listen to the Lakota audio and select the correct Lakota word.', 2, 0, 0, 2, '1', '2018-04-11 08:17:50', '2018-04-11 08:17:50'),
(56, 'multiple-choice', NULL, 'Word', 'a', 'e', 'Listen to the Lakota audio and select the correct English word.', 0, 0, 0, 3, '1', '2018-04-11 08:17:50', '2018-04-11 08:17:50'),
(57, 'multiple-choice', NULL, 'Word', 'i', 'a', 'Look at the picture and select the correct Lakota audio.', 0, 0, 0, 3, '0', '2018-04-11 08:17:50', '2018-04-11 08:17:50'),
(58, 'multiple-choice', NULL, 'Word', 'i', 'l', 'Look at the picture and select the correct Lakota word.', 4, 0, 0, 0, '1', '2018-04-11 08:17:50', '2018-04-11 08:17:50'),
(59, 'multiple-choice', NULL, 'Word', 'l', 'a', 'Read the Lakota word and select the correct Lakota audio.', 1, 0, 0, 2, '0', '2018-04-11 08:17:50', '2018-04-11 08:17:50'),
(60, 'multiple-choice', NULL, 'Word', 'l', 'i', 'Read the Lakota word and select the correct picture.', 4, 0, 0, 0, '1', '2018-04-11 08:17:50', '2018-04-11 08:17:50'),
(61, 'multiple-choice', NULL, 'Word', 'l', 'l', '', 5, 0, 0, 0, '0', '2018-04-11 08:17:50', '2018-04-11 08:17:50'),
(62, 'multiple-choice', NULL, 'Word', 'l', 'e', 'Read the Lakota word and select the correct English word.', 4, 0, 0, 0, '1', '2018-04-11 08:17:50', '2018-04-11 08:17:50'),
(63, 'multiple-choice', NULL, 'Word', 'e', 'a', 'Read the English word and select the correct Lakota audio.', 0, 0, 0, 3, '0', '2018-04-11 08:17:50', '2018-04-11 08:17:50'),
(64, 'multiple-choice', NULL, 'Word', 'e', 'l', 'Read the English word and select the correct Lakota word.', 4, 0, 0, 0, '1', '2018-04-11 08:17:50', '2018-04-11 08:17:50'),
(65, 'multiple-choice', NULL, 'Pattern', 'a', 'a', '', 0, 0, 0, 5, '0', '2018-04-11 08:17:50', '2018-04-11 08:17:50'),
(66, 'multiple-choice', NULL, 'Pattern', 'a', 'i', 'Listen to the Lakota audio and select the correct picture.', 0, 0, 0, 6, '1', '2018-04-11 08:17:51', '2018-04-11 08:17:51'),
(67, 'multiple-choice', NULL, 'Pattern', 'a', 'l', 'Listen to the Lakota audio and select the correct Lakota sentence.', 5, 0, 0, 5, '1', '2018-04-11 08:17:51', '2018-04-11 08:17:51'),
(68, 'multiple-choice', NULL, 'Pattern', 'a', 'e', 'Listen to the Lakota audio and select the correct English sentence.', 0, 0, 0, 7, '1', '2018-04-11 08:17:51', '2018-04-11 08:17:51'),
(69, 'multiple-choice', NULL, 'Pattern', 'i', 'a', 'Look at the picture and select the correct Lakota audio.', 0, 0, 0, 6, '0', '2018-04-11 08:17:51', '2018-04-11 08:17:51'),
(70, 'multiple-choice', NULL, 'Pattern', 'i', 'l', 'Look at the picture and select the correct Lakota sentence.', 7, 0, 0, 0, '1', '2018-04-11 08:17:51', '2018-04-11 08:17:51'),
(71, 'multiple-choice', NULL, 'Pattern', 'l', 'a', 'Read the Lakota sentence and select the correct Lakota audio.', 1, 0, 0, 5, '0', '2018-04-11 08:17:51', '2018-04-11 08:17:51'),
(72, 'multiple-choice', NULL, 'Pattern', 'l', 'i', 'Read the Lakota sentence and select the correct picture.', 6, 0, 0, 0, '1', '2018-04-11 08:17:51', '2018-04-11 08:17:51'),
(73, 'multiple-choice', NULL, 'Pattern', 'l', 'l', '', 8, 0, 0, 0, '0', '2018-04-11 08:17:51', '2018-04-11 08:17:51'),
(74, 'multiple-choice', NULL, 'Pattern', 'l', 'e', 'Read the Lakota sentence and select the correct English sentence.', 7, 0, 0, 0, '1', '2018-04-11 08:17:51', '2018-04-11 08:17:51'),
(75, 'multiple-choice', NULL, 'Pattern', 'e', 'a', 'Read the English sentence and select the correct Lakota audio.', 0, 0, 0, 6, '0', '2018-04-11 08:17:51', '2018-04-11 08:17:51'),
(76, 'multiple-choice', NULL, 'Pattern', 'e', 'l', 'Read the English sentence and select the correct Lakota sentence.', 7, 0, 0, 0, '1', '2018-04-11 08:17:51', '2018-04-11 08:17:51'),
(77, 'recording', NULL, 'Word', 'a', 'r', 'Listen to the Lakota audio and record yourself saying the correct Lakota word.', 0, 0, 2, 0, '1', '2018-04-11 08:17:51', '2018-04-11 08:17:51'),
(78, 'recording', NULL, 'Word', 'i', 'r', 'Look at the picture and record yourself saying the correct Lakota word.', 0, 0, 3, 0, '1', '2018-04-11 08:17:51', '2018-04-11 08:17:51'),
(79, 'recording', NULL, 'Word', 'l', 'r', 'Read the Lakota word and record yourself saying the correct Lakota word.', 2, 0, 3, 0, '1', '2018-04-11 08:17:51', '2018-04-11 08:17:51'),
(80, 'recording', NULL, 'Word', 'e', 'r', 'Read the English word and record yourself saying the correct Lakota word.', 0, 0, 3, 0, '1', '2018-04-11 08:17:51', '2018-04-11 08:17:51'),
(81, 'recording', NULL, 'Pattern', 'a', 'r', 'Listen to the Lakota audio and record yourself saying the correct Lakota sentence.', 0, 0, 5, 0, '1', '2018-04-11 08:17:51', '2018-04-11 08:17:51'),
(82, 'recording', NULL, 'Pattern', 'i', 'r', 'Look at the picture and record yourself saying the correct Lakota sentence.', 0, 0, 6, 0, '1', '2018-04-11 08:17:51', '2018-04-11 08:17:51'),
(83, 'recording', NULL, 'Pattern', 'l', 'r', 'Read the Lakota sentence and record yourself saying the correct Lakota sentence.', 2, 0, 5, 0, '1', '2018-04-11 08:17:51', '2018-04-11 08:17:51'),
(84, 'recording', NULL, 'Pattern', 'e', 'r', 'Read the English sentence and record yourself saying the correct Lakota sentence.', 0, 0, 6, 0, '1', '2018-04-11 08:17:51', '2018-04-11 08:17:51'),
(85, 'Type Answer', NULL, 'Word', 'a', 'l', 'Listen to the Lakota audio and type the correct Lakota word.', 0, 6, 0, 2, '1', '2018-04-11 08:17:51', '2018-04-11 08:17:51'),
(86, 'Type Answer', NULL, 'Word', 'i', 'l', 'Look at the picture and type the correct Lakota word.', 0, 7, 0, 0, '1', '2018-04-11 08:17:51', '2018-04-11 08:17:51'),
(87, 'Type Answer', NULL, 'Word', 'l', 'l', '', 2, 7, 0, 0, '0', '2018-04-11 08:17:51', '2018-04-11 08:17:51'),
(88, 'Type Answer', NULL, 'Word', 'e', 'l', 'Read the English word and type the correct Lakota word.', 0, 7, 0, 0, '1', '2018-04-11 08:17:51', '2018-04-11 08:17:51'),
(89, 'Type Answer', NULL, 'Pattern', 'a', 'l', 'Listen to the Lakota audio and type the correct Lakota sentence.', 0, 9, 0, 5, '1', '2018-04-11 08:17:52', '2018-04-11 08:17:52'),
(90, 'Type Answer', NULL, 'Pattern', 'i', 'l', 'Look at the picture and type the correct Lakota sentence.', 0, 10, 0, 0, '1', '2018-04-11 08:17:52', '2018-04-11 08:17:52'),
(91, 'Type Answer', NULL, 'Pattern', 'l', 'l', '', 2, 10, 0, 0, '0', '2018-04-11 08:17:52', '2018-04-11 08:17:52'),
(92, 'Type Answer', NULL, 'Pattern', 'e', 'l', 'Read the English sentence and type the correct Lakota sentence.', 0, 10, 0, 0, '1', '2018-04-11 08:17:52', '2018-04-11 08:17:52'),
(93, 'Rearrange words', NULL, 'Pattern', 'a', 'l', 'Listen to the Lakota audio and rearrange the words to create the correct Lakota sentence.', 2, 2, 0, 3, '1', '2018-04-11 08:17:52', '2018-04-11 08:17:52'),
(94, 'Rearrange words', NULL, 'Pattern', 'i', 'l', 'Look at the picture and rearrange the words to create the correct Lakota sentence.', 2, 6, 0, 0, '1', '2018-04-11 08:17:52', '2018-04-11 08:17:52'),
(95, 'Rearrange words', NULL, 'Pattern', 'l', 'l', '', 3, 6, 0, 0, '0', '2018-04-11 08:17:52', '2018-04-11 08:17:52'),
(96, 'Rearrange words', NULL, 'Pattern', 'e', 'l', 'Read the English sentence and rearrange the words to create the correct Lakota sentence.', 2, 6, 0, 0, '1', '2018-04-11 08:17:52', '2018-04-11 08:17:52'),
(97, 'Building Blocks', NULL, 'Pattern', 'a', 'l', 'Listen to the Lakota audio and rearrange the words to create the correct Lakota sentence.', 2, 4, 0, 3, '1', '2018-04-11 08:17:52', '2018-04-11 08:17:52'),
(98, 'Building Blocks', NULL, 'Pattern', 'i', 'l', 'Look at the picture and rearrange the words to create the correct Lakota sentence.', 2, 7, 0, 0, '1', '2018-04-11 08:17:52', '2018-04-11 08:17:52'),
(99, 'Building Blocks', NULL, 'Pattern', 'l', 'l', '', 3, 8, 0, 0, '0', '2018-04-11 08:17:52', '2018-04-11 08:17:52'),
(100, 'Building Blocks', NULL, 'Pattern', 'e', 'l', 'Read the English sentence and rearrange the words to create the correct Lakota sentence.', 2, 7, 0, 0, '1', '2018-04-11 08:17:52', '2018-04-11 08:17:52'),
(101, 'Word Fill (Type)', NULL, 'Pattern', 'a', 'l', 'Listen to the Lakota audio and type the missing Lakota words to complete the sentence.', 0, 6, 0, 4, '1', '2018-04-11 08:17:52', '2018-04-11 08:17:52'),
(102, 'Word Fill (Type)', NULL, 'Pattern', 'i', 'l', 'Look at the picture and type the missing Lakota words to complete the sentence.', 0, 7, 0, 0, '1', '2018-04-11 08:17:52', '2018-04-11 08:17:52'),
(103, 'Word Fill (Type)', NULL, 'Pattern', 'l', 'l', '', 2, 7, 0, 0, '0', '2018-04-11 08:17:52', '2018-04-11 08:17:52'),
(104, 'Word Fill (Type)', NULL, 'Pattern', 'e', 'l', 'Read the English sentence and type the missing Lakota words to complete the sentence.', 0, 7, 0, 0, '1', '2018-04-11 08:17:52', '2018-04-11 08:17:52'),
(105, 'Word Fill (Multiple Choice)', NULL, 'Pattern', 'a', 'l', 'Listen to the Lakota audio and select the missing Lakota words that complete the sentence.', 3, 2, 0, 3, '1', '2018-04-11 08:17:52', '2018-04-11 08:17:52'),
(106, 'Word Fill (Multiple Choice)', NULL, 'Pattern', 'i', 'l', 'Look at the picture and select the missing Lakota words that complete the sentence.', 3, 2, 0, 0, '1', '2018-04-11 08:17:52', '2018-04-11 08:17:52'),
(107, 'Word Fill (Multiple Choice)', NULL, 'Pattern', 'l', 'l', '', 3, 3, 0, 0, '0', '2018-04-11 08:17:52', '2018-04-11 08:17:52'),
(108, 'Word Fill (Multiple Choice)', NULL, 'Pattern', 'e', 'l', 'Read the English sentence and select the missing Lakota words that complete the sentence.', 3, 2, 0, 0, '1', '2018-04-11 08:17:52', '2018-04-11 08:17:52'),
(109, 'fill_in_the_blanks_mcq', NULL, 'Pattern', 'e', 'l', 'Read the English word and select the missing letters that spell the correct Lakota word.', 0, 4, 0, 0, '1', '2018-04-11 08:17:49', '2018-04-11 08:17:49'),
(110, 'match-the-pair', NULL, 'Pattern', 'a', 'e', 'Match the Lakota audio to the correct English word.', 0, 0, 0, 4, '1', '2018-04-11 08:17:48', '2018-04-11 08:17:48'),
(111, 'match-the-pair', NULL, 'Pattern', 'a', 'l', 'Match the Lakota audio to the correct Lakota word.', 2, 0, 0, 2, '1', '2018-04-11 08:17:48', '2018-04-11 08:17:48'),
(112, 'match-the-pair', NULL, 'Pattern', 'l', 'i', 'Match the Lakota pattern to the correct picture.', 4, 0, 0, 0, '1', '2022-11-09 08:17:48', '2022-11-09 08:17:48'),
(113, 'fill_in_the_blanks_typing', NULL, 'Pattern', 'e', 'l', 'Read the English pattern and fill in the missing letters to spell the correct Lakota.', 0, 5, 0, 0, '1', '2018-04-11 08:17:48', '2018-04-11 08:17:48'),
(114, 'match-the-pair', NULL, 'Pattern', 'e', 'l', 'Match the English to the correct Lakota.', 3, 0, 0, 0, '1', '2018-04-11 08:17:48', '2018-04-11 08:17:48'),
(115, 'match-the-pair', NULL, 'Pattern', 'i', 'l', 'Match the picture to the correct Lakota.', 3, 0, 0, 0, '1', '2018-04-11 08:17:48', '2018-04-11 08:17:48'),
(116, 'anagram', NULL, 'Pattern', 'e', 'l', 'Read the English phrase and rearrange the letters to make the correct Lakota phrase.', 0, 7, 0, 0, '1', '2018-04-11 08:17:48', '2018-04-11 08:17:48'),
(117, 'fill_in_the_blanks_typing', NULL, 'Word', 'l', 'e', '', 3, 0, 0, 0, '0', '2018-04-11 08:17:48', '2018-04-11 08:17:48'),
(118, 'match-the-pair', NULL, 'Pattern', 'i', 'e', 'Match the image to the correct English text.', 4, 0, 0, 0, '1', '2018-04-11 08:17:48', '2018-04-11 08:17:48'),
(119, 'multiple-choice', NULL, 'Pattern', 'e', 'i', 'Read the English text and select the correct image.', 4, 0, 0, 0, '1', '2018-04-11 08:17:50', '2018-04-11 08:17:50');

-- --------------------------------------------------------

--
-- Table structure for table `progress_timers`
--

CREATE TABLE `progress_timers` (
  `id` int(10) UNSIGNED NOT NULL,
  `path_id` int(10) UNSIGNED DEFAULT NULL,
  `level_id` int(10) UNSIGNED DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `unit_id` int(10) UNSIGNED DEFAULT NULL,
  `timer_type` enum('review','path') COLLATE latin1_general_ci DEFAULT NULL,
  `minute_spent` int(10) UNSIGNED DEFAULT '0',
  `entry_date` date DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `progress_timers`
--

INSERT INTO `progress_timers` (`id`, `path_id`, `level_id`, `user_id`, `unit_id`, `timer_type`, `minute_spent`, `entry_date`, `created`, `modified`) VALUES
(1, 1, 2, 1, 1, 'path', 2, '2024-09-15', '2024-09-15 10:47:51', '2024-09-15 10:48:51'),
(2, 1, 4, 1, 9, 'path', 4, '2024-09-16', '2024-09-16 07:56:46', '2024-09-16 12:22:01'),
(3, 1, 5, 1, 15, 'path', 17, '2024-09-16', '2024-09-16 12:35:42', '2024-09-16 13:04:26'),
(4, 1, 2, 1, 1, 'path', 143, '2024-09-16', '2024-09-16 13:06:27', '2024-09-16 16:57:32'),
(5, 1, 5, 1, 13, 'path', 50, '2024-09-16', '2024-09-16 16:58:41', '2024-09-16 17:54:15'),
(6, 1, 3, 1, 16, 'path', 152, '2024-09-16', '2024-09-16 18:10:05', '2024-09-16 20:39:04'),
(7, 1, 3, 1, 16, 'path', 66, '2024-09-17', '2024-09-16 22:34:43', '2024-09-17 15:41:01'),
(8, 1, 3, 1, 16, 'path', 1, '2024-09-17', '2024-09-16 22:34:43', '2024-09-16 22:34:43'),
(9, 1, 3, 1, 17, 'path', 58, '2024-09-17', '2024-09-17 15:42:33', '2024-09-17 21:03:08'),
(10, 1, 3, 1, 17, 'path', 47, '2024-09-18', '2024-09-17 23:39:26', '2024-09-18 11:10:23'),
(11, 1, 3, 1, 16, 'path', 21, '2024-09-18', '2024-09-18 07:07:13', '2024-09-18 07:27:15'),
(12, 1, 3, 1, 19, 'path', 191, '2024-09-18', '2024-09-18 11:12:13', '2024-09-18 17:13:24'),
(13, 1, 3, 1, 19, 'path', 2, '2024-09-21', '2024-09-21 09:27:20', '2024-09-21 09:28:20'),
(14, 1, 3, 1, 19, 'path', 133, '2024-09-24', '2024-09-24 05:21:33', '2024-09-24 21:32:28'),
(15, 1, 3, 1, 19, 'path', 35, '2024-09-25', '2024-09-24 23:45:28', '2024-09-25 21:34:42'),
(16, 1, 3, 1, 19, 'path', 420, '2024-09-26', '2024-09-25 23:21:16', '2024-09-27 06:40:33'),
(17, 1, 3, 1, 19, 'path', 77, '2024-09-27', '2024-09-26 22:00:41', '2024-09-27 18:11:28'),
(18, 1, 3, 1, 17, 'path', 77, '2024-09-27', '2024-09-27 18:10:36', '2024-09-27 19:34:19'),
(19, 1, 6, 1, 20, 'path', 24, '2024-09-27', '2024-09-27 18:41:58', '2024-09-27 19:12:05'),
(20, 1, 6, 1, 21, 'path', 155, '2024-09-27', '2024-09-27 19:13:34', '2024-09-28 05:05:29'),
(21, 1, 6, 1, 21, 'path', 427, '2024-09-28', '2024-09-28 07:27:46', '2024-09-28 23:25:07'),
(22, 1, 6, 1, 21, 'path', 1, '2024-09-28', '2024-09-28 07:27:46', '2024-09-28 07:27:46'),
(23, 1, NULL, 1, NULL, 'review', 2, '2024-10-10', '2024-10-10 23:39:14', '2024-10-11 00:35:33'),
(24, 1, NULL, 1, NULL, 'review', 297, '2024-10-11', '2024-10-11 18:19:49', '2024-10-12 06:27:06'),
(25, 1, NULL, 1, NULL, 'review', 144, '2024-10-12', '2024-10-12 08:39:57', '2024-10-13 05:05:38'),
(26, 1, NULL, 1, NULL, 'review', 200, '2024-10-13', '2024-10-13 07:40:49', '2024-10-14 05:32:54'),
(27, 1, NULL, 1, NULL, 'review', 442, '2024-10-14', '2024-10-14 08:00:03', '2024-10-15 06:49:46'),
(28, 1, 6, 1, 20, 'path', 10, '2024-10-14', '2024-10-15 01:29:26', '2024-10-15 01:37:21'),
(29, 1, NULL, 1, NULL, 'review', 161, '2024-10-15', '2024-10-15 09:09:23', '2024-10-15 18:54:42'),
(30, 1, 6, 1, 21, 'path', 215, '2024-10-15', '2024-10-15 18:57:17', '2024-10-16 05:42:12'),
(31, 1, 6, 1, 21, 'path', 233, '2024-10-16', '2024-10-16 07:17:34', '2024-10-16 23:38:08'),
(32, 1, NULL, 1, NULL, 'review', 137, '2024-10-16', '2024-10-16 23:41:16', '2024-10-17 04:42:28'),
(33, 1, NULL, 1, NULL, 'review', 212, '2024-10-17', '2024-10-17 07:32:22', '2024-10-17 19:31:47'),
(34, 1, 3, 1, 16, 'path', 3, '2024-10-17', '2024-10-17 19:34:30', '2024-10-17 19:37:23'),
(35, 1, 3, 1, 17, 'path', 284, '2024-10-17', '2024-10-17 19:38:39', '2024-10-18 06:12:48'),
(36, 1, 3, 1, 17, 'path', 329, '2024-10-18', '2024-10-18 08:12:25', '2024-10-19 06:20:47'),
(37, 1, 3, 1, 17, 'path', 1, '2024-10-18', '2024-10-18 08:12:25', '2024-10-18 08:12:25'),
(38, 1, 3, 1, 17, 'path', 13, '2024-10-19', '2024-10-19 08:51:55', '2024-10-20 05:31:13'),
(39, 1, 3, 1, 17, 'path', 317, '2024-10-20', '2024-10-20 07:37:44', '2024-10-21 05:15:19'),
(40, 1, 3, 1, 17, 'path', 416, '2024-10-21', '2024-10-21 08:02:42', '2024-10-22 05:46:31'),
(41, 1, 3, 1, 19, 'path', 95, '2024-10-21', '2024-10-21 16:40:31', '2024-10-21 20:19:29'),
(42, 1, 3, 1, 16, 'path', 34, '2024-10-21', '2024-10-21 20:47:44', '2024-10-21 21:06:22'),
(43, 1, 3, 1, 17, 'path', 274, '2024-10-22', '2024-10-22 08:14:26', '2024-10-22 19:27:23'),
(44, 1, 3, 1, 17, 'path', 1, '2024-10-22', '2024-10-22 08:14:26', '2024-10-22 08:14:26'),
(45, 1, 3, 1, 16, 'path', 149, '2024-10-22', '2024-10-22 19:26:47', '2024-10-23 05:50:56'),
(46, 1, 3, 1, 16, 'path', 148, '2024-10-23', '2024-10-23 07:35:50', '2024-10-23 17:48:25'),
(47, 1, 3, 1, 16, 'path', 1, '2024-10-23', '2024-10-23 07:35:50', '2024-10-23 07:35:50'),
(48, 1, 3, 1, 17, 'path', 148, '2024-10-23', '2024-10-23 17:08:57', '2024-10-23 20:37:54'),
(49, 1, 3, 1, 19, 'path', 3, '2024-10-23', '2024-10-23 20:40:34', '2024-10-24 03:54:29'),
(50, 1, 6, 1, 21, 'path', 40, '2024-10-23', '2024-10-23 20:42:39', '2024-10-24 03:50:31'),
(51, 1, 6, 1, 20, 'path', 12, '2024-10-23', '2024-10-23 21:21:46', '2024-10-24 03:47:20'),
(52, 1, 6, 1, 21, 'path', 352, '2024-10-25', '2024-10-25 17:05:22', '2024-10-26 06:53:30'),
(53, 1, 6, 1, 21, 'path', 576, '2024-10-26', '2024-10-26 07:10:29', '2024-10-27 06:28:23'),
(54, 1, 6, 1, 21, 'path', 408, '2024-10-27', '2024-10-27 08:19:03', '2024-10-28 04:39:25'),
(55, 1, 6, 1, 21, 'path', 86, '2024-10-28', '2024-10-28 07:13:18', '2024-10-28 16:30:42'),
(56, 1, 3, 1, 17, 'path', 237, '2024-10-28', '2024-10-28 16:32:51', '2024-10-29 06:50:39'),
(57, 1, 3, 1, 17, 'path', 167, '2024-10-29', '2024-10-29 07:06:39', '2024-10-29 16:48:46'),
(58, 1, 7, 1, 22, 'path', 11, '2024-10-29', '2024-10-29 16:50:22', '2024-10-29 17:01:26'),
(59, 1, 7, 1, 23, 'path', 379, '2024-10-29', '2024-10-29 17:02:36', '2024-10-30 06:29:30'),
(60, 1, 7, 1, 23, 'path', 379, '2024-10-30', '2024-10-30 07:46:23', '2024-10-31 05:57:58'),
(61, 1, 7, 1, 23, 'path', 25, '2024-10-31', '2024-10-31 08:20:29', '2024-10-31 15:39:42'),
(62, 1, 7, 1, 22, 'path', 312, '2024-10-31', '2024-10-31 15:41:19', '2024-11-01 05:24:54'),
(63, 1, 7, 1, 22, 'path', 153, '2024-11-01', '2024-11-01 07:15:53', '2024-11-02 06:07:27'),
(64, 1, 7, 1, 22, 'path', 175, '2024-11-02', '2024-11-02 08:49:21', '2024-11-02 16:35:49'),
(65, 1, 8, 1, 24, 'path', 35, '2024-11-02', '2024-11-02 16:35:12', '2024-11-02 17:22:22'),
(66, 1, 8, 1, 25, 'path', 37, '2024-11-02', '2024-11-02 17:26:32', '2024-11-02 17:55:32'),
(67, 1, 8, 1, 26, 'path', 18, '2024-11-02', '2024-11-02 17:55:39', '2024-11-03 06:28:29'),
(68, 1, 8, 1, 26, 'path', 328, '2024-11-03', '2024-11-03 08:07:57', '2024-11-04 06:13:10'),
(69, 1, 8, 1, 27, 'path', 14, '2024-11-03', '2024-11-03 22:18:47', '2024-11-03 22:29:03'),
(70, 1, 8, 1, 28, 'path', 3, '2024-11-03', '2024-11-03 22:27:37', '2024-11-03 22:30:17'),
(71, 1, 3, 1, 17, 'path', 1, '2024-11-03', '2024-11-03 22:41:00', '2024-11-03 22:41:00'),
(72, 1, 8, 1, 26, 'path', 246, '2024-11-04', '2024-11-04 08:15:24', '2024-11-05 06:22:38'),
(73, 1, 8, 1, 26, 'path', 425, '2024-11-05', '2024-11-05 07:41:48', '2024-11-06 03:46:59'),
(74, 1, 8, 1, 27, 'path', 47, '2024-11-05', '2024-11-06 02:42:09', '2024-11-06 03:43:36'),
(75, 1, 8, 1, 25, 'path', 2, '2024-11-05', '2024-11-06 03:42:13', '2024-11-06 03:43:13'),
(76, 1, 3, 1, 17, 'path', 1, '2024-11-05', '2024-11-06 03:49:32', '2024-11-06 03:49:32'),
(77, 1, 6, 1, 21, 'path', 7, '2024-11-05', '2024-11-06 03:50:55', '2024-11-06 03:57:34'),
(78, 1, 9, 1, 29, 'path', 33, '2024-11-05', '2024-11-06 03:58:48', '2024-11-06 06:16:21'),
(79, 1, 9, 1, 29, 'path', 349, '2024-11-06', '2024-11-06 08:10:47', '2024-11-07 06:31:42'),
(80, 1, 9, 1, 29, 'path', 1, '2024-11-06', '2024-11-06 08:10:47', '2024-11-06 08:10:47'),
(81, 1, 9, 1, 29, 'path', 262, '2024-11-07', '2024-11-07 09:04:16', '2024-11-07 23:57:23'),
(82, 1, 9, 1, 32, 'path', 4, '2024-11-07', '2024-11-07 19:53:40', '2024-11-07 19:57:23'),
(83, 1, 10, 1, 31, 'path', 2, '2024-11-07', '2024-11-07 19:56:29', '2024-11-07 19:57:29'),
(84, 1, 10, 1, 30, 'path', 163, '2024-11-07', '2024-11-07 19:58:50', '2024-11-08 00:00:34'),
(85, 1, 7, 1, 22, 'path', 131, '2024-11-07', '2024-11-08 00:03:59', '2024-11-08 05:25:56'),
(86, 1, 7, 1, 22, 'path', 208, '2024-11-08', '2024-11-08 07:43:15', '2024-11-08 19:25:35'),
(87, 1, 7, 1, 34, 'path', 112, '2024-11-08', '2024-11-08 19:26:54', '2024-11-09 00:59:36'),
(88, 1, 6, 1, 36, 'path', 247, '2024-11-08', '2024-11-09 01:00:42', '2024-11-09 06:59:47'),
(89, 1, 6, 1, 36, 'path', 32, '2024-11-09', '2024-11-09 07:00:47', '2024-11-09 10:12:40'),
(90, 1, 6, 1, 36, 'path', 1, '2024-11-09', '2024-11-09 07:00:47', '2024-11-09 07:00:47'),
(91, 1, 6, 1, 36, 'path', 1, '2024-11-09', '2024-11-09 07:00:47', '2024-11-09 07:00:47'),
(92, 1, 3, 1, 35, 'path', 6, '2024-12-11', '2024-12-11 17:17:18', '2024-12-11 17:35:37'),
(93, 1, 10, 1, 30, 'path', 121, '2025-03-15', '2025-03-15 19:10:30', '2025-03-16 06:50:09'),
(94, 1, 10, 1, 30, 'path', 450, '2025-03-16', '2025-03-16 07:07:09', '2025-03-17 06:52:09'),
(95, 1, 10, 1, 30, 'path', 736, '2025-03-17', '2025-03-17 07:08:29', '2025-03-18 06:57:52'),
(96, 1, 10, 1, 30, 'path', 659, '2025-03-18', '2025-03-18 07:14:49', '2025-03-19 06:50:33'),
(97, 1, 10, 1, 30, 'path', 270, '2025-03-19', '2025-03-19 07:08:02', '2025-03-19 18:50:36'),
(98, 1, 9, 1, 29, 'path', 702, '2025-03-19', '2025-03-19 18:52:17', '2025-03-20 06:59:55'),
(99, 1, 9, 1, 29, 'path', 1139, '2025-03-20', '2025-03-20 07:00:55', '2025-03-21 06:59:22'),
(100, 1, 9, 1, 29, 'path', 389, '2025-03-21', '2025-03-21 07:00:22', '2025-03-22 00:30:17'),
(101, 1, 9, 1, 32, 'path', 113, '2025-03-21', '2025-03-21 21:57:13', '2025-03-22 06:52:46'),
(102, 1, NULL, 1, NULL, 'review', 20, '2025-03-21', '2025-03-21 23:55:24', '2025-03-22 00:26:29'),
(103, 1, 9, 1, 32, 'path', 236, '2025-03-22', '2025-03-22 07:09:31', '2025-03-23 04:24:03'),
(104, 1, 9, 1, 32, 'path', 1, '2025-03-22', '2025-03-22 07:09:31', '2025-03-22 07:09:31'),
(105, 1, 9, 1, 29, 'path', 3, '2025-03-22', '2025-03-23 04:21:26', '2025-03-23 04:23:26'),
(106, 1, 10, 1, 30, 'path', 31, '2025-03-22', '2025-03-23 04:22:01', '2025-03-23 06:49:36'),
(107, 1, 10, 1, 30, 'path', 99, '2025-03-23', '2025-03-23 07:07:40', '2025-03-24 05:13:52'),
(108, 1, 10, 1, 30, 'path', 890, '2025-03-24', '2025-03-24 07:21:10', '2025-03-25 06:59:46'),
(109, 1, 10, 1, 30, 'path', 566, '2025-03-25', '2025-03-25 07:00:46', '2025-03-25 16:59:18'),
(110, 1, 9, 1, 29, 'path', 2, '2025-03-25', '2025-03-25 16:58:42', '2025-03-25 16:59:42'),
(111, 1, 9, 1, 32, 'path', 29, '2025-03-25', '2025-03-25 16:59:03', '2025-03-25 17:26:13'),
(112, 1, 3, 1, 35, 'path', 6, '2025-04-30', '2025-04-30 18:39:48', '2025-04-30 19:00:50'),
(113, 1, 3, 1, 35, 'path', 135, '2025-05-01', '2025-05-01 18:07:03', '2025-05-01 20:37:40'),
(114, 1, 3, 1, 19, 'path', 4, '2025-05-01', '2025-05-01 18:41:06', '2025-05-01 18:44:06'),
(115, 1, 6, 1, 20, 'path', 114, '2025-05-01', '2025-05-01 18:45:48', '2025-05-01 20:39:41'),
(116, 1, 6, 1, 36, 'path', 9, '2025-05-01', '2025-05-01 20:41:47', '2025-05-01 20:49:47'),
(117, 1, 7, 1, 22, 'path', 2, '2025-05-01', '2025-05-01 20:51:31', '2025-05-01 20:52:31'),
(118, 1, 7, 1, 33, 'path', 3, '2025-05-01', '2025-05-01 20:54:08', '2025-05-01 20:56:08'),
(119, 1, 7, 1, 34, 'path', 2, '2025-05-01', '2025-05-01 20:57:23', '2025-05-01 20:58:23'),
(120, 1, 8, 1, 26, 'path', 1, '2025-05-01', '2025-05-01 21:00:09', '2025-05-01 21:00:09'),
(121, 1, 8, 1, 27, 'path', 6, '2025-05-01', '2025-05-01 21:02:00', '2025-05-01 21:52:56'),
(122, 1, 8, 1, 28, 'path', 45, '2025-05-01', '2025-05-01 21:04:08', '2025-05-01 21:48:08'),
(123, 1, 9, 1, 29, 'path', 1, '2025-05-01', '2025-05-01 21:54:39', '2025-05-01 21:54:39'),
(124, 1, 9, 1, 32, 'path', 3, '2025-05-01', '2025-05-01 21:57:15', '2025-05-01 21:59:15'),
(125, 1, 10, 1, 30, 'path', 2, '2025-05-01', '2025-05-01 22:01:01', '2025-05-01 22:02:01'),
(126, 1, 3, 1, 17, 'path', 148, '2025-05-01', '2025-05-01 22:03:21', '2025-05-02 06:04:53'),
(127, 1, 3, 1, 17, 'path', 628, '2025-05-02', '2025-05-02 08:56:29', '2025-05-03 06:57:15'),
(128, 1, 3, 1, 17, 'path', 506, '2025-05-03', '2025-05-03 07:15:07', '2025-05-03 19:03:59'),
(129, 1, 3, 1, 35, 'path', 584, '2025-05-03', '2025-05-03 19:06:15', '2025-05-04 06:59:02'),
(130, 1, 3, 1, 35, 'path', 928, '2025-05-04', '2025-05-04 07:00:02', '2025-05-04 22:30:59'),
(131, 1, 3, 1, 17, 'path', 4, '2025-05-04', '2025-05-04 22:33:25', '2025-05-04 22:39:29'),
(132, 1, 9, 1, 29, 'path', 15, '2025-05-04', '2025-05-04 22:44:15', '2025-05-04 22:59:10'),
(133, 1, 10, 1, 30, 'path', 333, '2025-05-04', '2025-05-04 22:51:53', '2025-05-05 06:48:14'),
(134, 1, 10, 1, 30, 'path', 756, '2025-05-05', '2025-05-05 07:10:01', '2025-05-06 06:25:00'),
(135, 1, 10, 1, 30, 'path', 172, '2025-05-06', '2025-05-06 08:15:53', '2025-05-07 00:45:04'),
(136, 1, 3, 1, 37, 'path', 10, '2025-05-07', '2025-05-07 23:04:44', '2025-05-07 23:16:55'),
(137, 1, 6, 1, 38, 'path', 12, '2025-05-07', '2025-05-07 23:18:28', '2025-05-07 23:30:13'),
(138, 1, 3, 1, 37, 'path', 1, '2025-05-14', '2025-05-14 17:51:46', '2025-05-14 17:51:46'),
(139, 1, 3, 1, 19, 'path', 43, '2025-05-14', '2025-05-14 17:55:04', '2025-05-14 18:41:34'),
(140, 1, 9, 1, 32, 'path', 17, '2025-05-14', '2025-05-14 19:00:29', '2025-05-14 19:16:52'),
(141, 1, 2, 1, 1, 'path', 215, '2025-05-14', '2025-05-14 19:18:47', '2025-05-15 00:03:18'),
(142, 1, NULL, 1, NULL, 'review', 273, '2025-05-14', '2025-05-14 19:20:23', '2025-05-15 05:43:01'),
(143, 1, NULL, 1, NULL, 'review', 146, '2025-05-15', '2025-05-15 08:12:55', '2025-05-15 18:42:09'),
(144, 1, 9, 3, 29, 'path', 5, '2025-07-10', '2025-07-10 16:43:02', '2025-07-10 16:57:06'),
(145, 1, 9, 1, 29, 'path', 7, '2025-07-10', '2025-07-11 01:36:30', '2025-07-11 04:22:33'),
(146, 1, NULL, 1, NULL, 'review', 6, '2025-07-18', '2025-07-18 21:55:47', '2025-07-18 22:33:32');

-- --------------------------------------------------------

--
-- Table structure for table `recording_audios`
--

CREATE TABLE `recording_audios` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `exercise_id` int(11) UNSIGNED DEFAULT NULL,
  `file_name` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `aws_link` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `type` enum('exercise','review') COLLATE latin1_general_ci DEFAULT 'exercise',
  `created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reference_dictionary`
--

CREATE TABLE `reference_dictionary` (
  `id` int(11) UNSIGNED NOT NULL,
  `audio` int(11) UNSIGNED DEFAULT NULL,
  `lakota` varchar(255) NOT NULL,
  `english` varchar(255) NOT NULL,
  `morphology` varchar(255) DEFAULT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `full_entry` text,
  `part_of_speech` varchar(255) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `review_counters`
--

CREATE TABLE `review_counters` (
  `id` int(11) NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `level_id` int(11) UNSIGNED DEFAULT NULL,
  `unit_id` int(11) UNSIGNED DEFAULT NULL,
  `counter` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `review_counters`
--

INSERT INTO `review_counters` (`id`, `user_id`, `level_id`, `unit_id`, `counter`, `created`, `modified`) VALUES
(1, 1, 3, 19, 3, '2024-10-11 19:28:45', '2024-10-11 20:56:42'),
(2, 1, 6, 20, 34, '2024-10-11 21:06:02', '2024-10-16 23:54:27');

-- --------------------------------------------------------

--
-- Table structure for table `review_queues`
--

CREATE TABLE `review_queues` (
  `id` int(11) NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `card_id` int(11) UNSIGNED DEFAULT NULL,
  `skill_type` enum('reading','writing','speaking','listening') COLLATE latin1_general_ci DEFAULT NULL,
  `xp_1` float(11,2) DEFAULT NULL,
  `xp_2` float(11,2) DEFAULT NULL,
  `xp_3` float(11,2) DEFAULT NULL,
  `xp_4` float(11,2) DEFAULT NULL,
  `sort` int(11) NOT NULL DEFAULT '0',
  `num_times` int(11) NOT NULL DEFAULT '0',
  `daystamp` int(11) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `review_queues`
--

INSERT INTO `review_queues` (`id`, `user_id`, `card_id`, `skill_type`, `xp_1`, `xp_2`, `xp_3`, `xp_4`, `sort`, `num_times`, `daystamp`, `created`, `modified`) VALUES
(1, 1, 1, 'reading', 0.00, 0.00, 0.00, 0.00, 20918, 232, 20222, '2024-09-15 16:26:36', '2025-05-14 19:17:56'),
(2, 1, 1, 'writing', 0.00, 0.00, 1.00, 0.00, 20919, 232, 20222, '2024-09-15 16:26:36', '2025-05-14 19:17:56'),
(3, 1, 1, 'speaking', 0.00, 0.00, 0.00, 0.00, 20918, 232, 20222, '2024-09-15 16:26:36', '2025-05-14 19:17:56'),
(4, 1, 1, 'listening', 0.00, 0.00, 0.00, 0.00, 20918, 232, 20222, '2024-09-15 16:26:36', '2025-05-14 19:17:56'),
(5, 1, 2, 'reading', 0.00, 0.00, 1.00, 0.00, 20611, 146, 20172, '2024-09-17 15:49:27', '2025-03-25 16:57:28'),
(6, 1, 2, 'writing', 0.00, 0.00, 1.00, 0.00, 20611, 146, 20172, '2024-09-17 15:49:27', '2025-03-25 16:57:28'),
(7, 1, 2, 'speaking', 0.00, 0.00, 0.00, 0.00, 20610, 146, 20172, '2024-09-17 15:49:27', '2025-03-25 16:57:28'),
(8, 1, 2, 'listening', 0.00, 0.00, 0.00, 0.00, 20610, 146, 20172, '2024-09-17 15:49:27', '2025-03-25 16:57:28'),
(9, 1, 3, 'reading', 1.00, 1.00, 0.00, 1.00, 20602, 143, 20172, '2024-09-17 15:49:31', '2025-03-25 17:07:27'),
(10, 1, 3, 'writing', 1.00, 2.00, 0.00, 1.00, 20602, 143, 20172, '2024-09-17 15:49:31', '2025-03-25 17:07:27'),
(11, 1, 3, 'speaking', 0.00, 0.00, 0.00, 0.00, 20601, 143, 20172, '2024-09-17 15:49:31', '2025-03-25 17:07:27'),
(12, 1, 3, 'listening', 0.00, 0.00, 0.00, 0.00, 20601, 143, 20172, '2024-09-17 15:49:31', '2025-03-25 17:07:27'),
(13, 1, 4, 'reading', 0.00, 2.00, 0.00, 0.00, 20428, 86, 20168, '2024-09-18 11:19:08', '2025-03-21 23:54:20'),
(14, 1, 4, 'writing', 0.00, 0.00, 0.00, 0.00, 20426, 86, 20168, '2024-09-18 11:19:08', '2025-03-21 23:54:20'),
(15, 1, 4, 'speaking', 0.00, 0.00, 0.00, 0.00, 20426, 86, 20168, '2024-09-18 11:19:08', '2025-03-21 23:54:20'),
(16, 1, 4, 'listening', 0.00, 0.00, 0.00, 0.00, 20426, 86, 20168, '2024-09-18 11:19:08', '2025-03-21 23:54:20');

-- --------------------------------------------------------

--
-- Table structure for table `review_vars`
--

CREATE TABLE `review_vars` (
  `id` int(11) NOT NULL,
  `key` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `value` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `review_vars`
--

INSERT INTO `review_vars` (`id`, `key`, `value`) VALUES
(1, 'a', 1),
(2, 'b', 1),
(3, 'c', 3);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) UNSIGNED NOT NULL,
  `role` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role`) VALUES
(5, 'content developer'),
(4, 'moderator'),
(6, 'student'),
(1, 'superadmin'),
(2, 'teacher'),
(3, 'user');

-- --------------------------------------------------------

--
-- Table structure for table `schools`
--

CREATE TABLE `schools` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `image_id` int(11) DEFAULT NULL COMMENT 'image file link',
  `grade_low` varchar(255) DEFAULT NULL,
  `grade_high` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `schools`
--

INSERT INTO `schools` (`id`, `name`, `image_id`, `grade_low`, `grade_high`) VALUES
(1, 'School #1', 14, 'K', '12'),
(2, 'School #2', 10, 'K', '5');

-- --------------------------------------------------------

--
-- Table structure for table `school_levels`
--

CREATE TABLE `school_levels` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `level_id` int(11) NOT NULL,
  `owner_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `school_roles`
--

CREATE TABLE `school_roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `school_roles`
--

INSERT INTO `school_roles` (`id`, `name`) VALUES
(1, 'teacher'),
(2, 'substitute'),
(3, 'student');

-- --------------------------------------------------------

--
-- Table structure for table `school_users`
--

CREATE TABLE `school_users` (
  `id` int(11) NOT NULL,
  `student_id` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `f_name` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `l_name` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `school_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `school_users`
--

INSERT INTO `school_users` (`id`, `student_id`, `f_name`, `l_name`, `school_id`, `user_id`, `role_id`) VALUES
(1, NULL, '', '', 1, 3, 1),
(2, NULL, '', '', 1, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sitesettings`
--

CREATE TABLE `sitesettings` (
  `id` int(11) UNSIGNED NOT NULL,
  `display_name` varchar(250) DEFAULT NULL,
  `key` varchar(250) DEFAULT ' ',
  `value` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `sitesettings`
--

INSERT INTO `sitesettings` (`id`, `display_name`, `key`, `value`) VALUES
(1, 'Site Name', 'site_name', 'eLearning Demo'),
(2, 'Site Logo', 'site_logo', 'sitelogo.png'),
(3, 'Site Address', 'site_address', 'USA'),
(4, 'Site Email', 'site_email', 'support@demo.org'),
(5, 'Facebook Link', 'facebook_link', 'http://facebook.com/'),
(6, 'Twitter link', 'twitter_link', 'https://twitter.com/'),
(7, 'Site contact link', 'contact_link', 'https://twitter.com/'),
(9, 'Login Page Logo', 'login_logo', 'owoicon1527088207.png'),
(10, 'Under Construction', 'under_construction', 'N'),
(11, 'Under construction Html', 'under_construction_html', '<h1 style=\"color:#9f0b10\"><strong>We are doing some maintenance to improve your Lakota learning experience</strong>.</h1>\r\n\r\n<h2>The site will be back up soon!</h2>\r\n'),
(12, 'Setting Minors Can Access Village', 'setting_minors_can_access_village', '1'),
(13, 'Contact Email', 'contact_email', 'support@demo.org'),
(14, 'Setting Minors Can Access Leaderboard', 'setting_minors_can_access_leaderboard', '1'),
(15, 'Setting Minors Can Access Friends', 'setting_minors_can_access_friends', '1'),
(16, 'Feature Village', 'feature_village', '1'),
(17, 'Min Supported App Version', 'min_supported_app_version', '1.4.6'),
(18, 'Latest App Version', 'latest_app_version', '1.4.6');

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `description` text CHARACTER SET utf8,
  `type` tinyint(2) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`id`, `name`, `description`, `type`, `created`, `modified`) VALUES
(1, 'Lessons (1 Block Card, 1 Field)', 'Lessons (1 Block Card, 1 Field)', NULL, NULL, NULL),
(2, 'Lessons (1 Block Card, 2 Fields)', 'Lessons (1 Block Card, 2 Fields)', NULL, NULL, NULL),
(3, 'Lessons (1 Block Card, 3 Fields)', 'Lessons (1 Block Card, 3 Fields)', NULL, NULL, NULL),
(4, 'Lessons (1 Block Card, 4 Fields)', 'Lessons (1 Block Card, 4 Fields)', NULL, NULL, NULL),
(5, 'Lessons (1 Block Card, 5 Fields)', 'Lessons (1 Block Card, 5 Fields)', NULL, NULL, NULL),
(6, 'Lessons (2 Blocks Card, 1 Field)', 'Lessons (2 Blocks Card, 1 Field)', NULL, NULL, NULL),
(7, 'Lessons (2 Blocks Card, 2 Fields)', 'Lessons (2 Blocks Card, 2 Fields)', NULL, NULL, NULL),
(8, 'Lessons (2 Blocks Card, 3 Fields)', 'Lessons (2 Blocks Card, 3 Fields)', NULL, NULL, NULL),
(9, 'Lessons (2 Blocks Card, 4 Fields)', 'Lessons (2 Blocks Card, 4 Fields)', NULL, NULL, NULL),
(10, 'Lessons (2 Blocks Card, 5 Fields)', 'Lessons (2 Blocks Card, 5 Fields)', NULL, NULL, NULL),
(11, 'Lessons (3 Blocks Card, 1 Field)', 'Lessons (3 Blocks Card, 1 Field)', NULL, NULL, NULL),
(12, 'Lessons (3 Blocks Card, 2 Fields)', 'Lessons (3 Blocks Card, 2 Fields)', NULL, NULL, NULL),
(13, 'Lessons (3 Blocks Card, 3 Fields)', 'Lessons (3 Blocks Card, 3 Fields)', NULL, NULL, NULL),
(14, 'Lessons (3 Blocks Card, 4 Fields)', 'Lessons (3 Blocks Card, 4 Fields)', NULL, NULL, NULL),
(15, 'Lessons (3 Blocks Card, 5 Fields)', 'Lessons (3 Blocks Card, 5 Fields)', NULL, NULL, NULL),
(16, 'MCQ, 1 Card -> 3 Cards, 1 Field', 'MCQ, 1 Card -> 3 Cards, 1 Field', NULL, NULL, NULL),
(17, 'MCQ, 1 Card -> 3 Cards, 2 Fields', 'MCQ, 1 Card -> 3 Cards, 2 Fields', NULL, NULL, NULL),
(18, 'MCQ, 1 Card -> 3 Cards, 3 Fields', 'MCQ, 1 Card -> 3 Cards, 3 Fields', NULL, NULL, NULL),
(19, 'MCQ, 1 Card -> 4 Cards, 5 Fields', 'MCQ, 1 Card -> 4 Cards, 5 Fields', NULL, NULL, NULL),
(20, 'MTP, 4 Cards, 5 Fields', 'MTP, 4 Cards, 5 Fields', NULL, NULL, NULL),
(21, 'MTP, 4 Cards, 1 Field', 'MTP, 4 Cards, 1 Field', NULL, NULL, NULL),
(22, 'Anagram - Card, 3 Fields', 'Anagram - Card, 3 Fields', NULL, NULL, NULL),
(23, 'Anagram - Card, 1 Field', 'Anagram - Card, 1 Field', NULL, NULL, NULL),
(24, 'True False, Cards, 1 Field', 'True False, Cards, 1 Field', NULL, NULL, NULL),
(25, 'True False, Cards, 2 Fields', 'True False, Cards, 2 Fields', NULL, NULL, NULL),
(26, 'True False, Cards, 3 Fields', 'True False, Cards, 3 Fields', NULL, NULL, NULL),
(27, 'True False, HTML', 'True False, HTML', NULL, NULL, NULL),
(28, 'True False, Card Group', 'True False, Card Group', NULL, NULL, NULL),
(29, 'Fill-in Typing Cards', 'Fill-in Typing Cards', NULL, NULL, NULL),
(30, 'Fill-in MCQ Cards', 'Fill-in MCQ Cards', NULL, NULL, NULL),
(32, 'Fill-in Typing Custom', 'Fill-in Typing Custom', NULL, NULL, NULL),
(33, 'Anagram - Custom/Card', 'Anagram - Custom/Card', NULL, NULL, NULL),
(34, 'Anagram - Custom/Card Group', 'Anagram - Custom/Card Group', NULL, NULL, NULL),
(35, 'MCQ, Custom', 'MCQ, Custom', NULL, NULL, NULL),
(36, 'MTP, Custom', 'MTP, Custom', NULL, NULL, NULL),
(37, 'MCQ, Card Groups', 'MCQ, Card Groups', NULL, NULL, NULL),
(38, 'MTP, Card Group', 'MTP, Card Group', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `unit_details`
--

CREATE TABLE `unit_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `learningpath_id` int(11) UNSIGNED NOT NULL,
  `unit_id` int(11) UNSIGNED NOT NULL,
  `lesson_id` int(11) UNSIGNED DEFAULT NULL,
  `exercise_id` int(11) UNSIGNED DEFAULT NULL,
  `sequence` tinyint(3) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `unit_details`
--

INSERT INTO `unit_details` (`id`, `learningpath_id`, `unit_id`, `lesson_id`, `exercise_id`, `sequence`, `created`, `modified`) VALUES
(10, 1, 5, 4, NULL, 1, '2024-09-15 16:24:33', '2024-09-15 16:24:33'),
(15, 1, 10, 10, NULL, 1, '2024-09-15 16:44:28', '2024-09-15 16:44:28'),
(21, 1, 15, 15, NULL, 1, '2024-09-16 12:34:18', '2024-09-16 12:34:18'),
(22, 1, 1, 5, NULL, 1, '2024-09-16 13:05:19', '2024-09-16 13:05:19'),
(23, 1, 1, 16, NULL, 2, '2024-09-16 13:05:19', '2024-09-16 13:05:19'),
(24, 1, 1, 17, NULL, 3, '2024-09-16 13:05:19', '2024-09-16 13:05:19'),
(25, 1, 1, 18, NULL, 4, '2024-09-16 13:05:19', '2024-09-16 13:05:19'),
(26, 1, 1, 19, NULL, 5, '2024-09-16 13:05:19', '2024-09-16 13:05:19'),
(27, 1, 2, 3, NULL, 1, '2024-09-16 13:15:40', '2024-09-16 13:15:40'),
(28, 1, 2, 20, NULL, 2, '2024-09-16 13:15:40', '2024-09-16 13:15:40'),
(29, 1, 2, 21, NULL, 3, '2024-09-16 13:15:40', '2024-09-16 13:15:40'),
(30, 1, 2, 22, NULL, 4, '2024-09-16 13:15:40', '2024-09-16 13:15:40'),
(31, 1, 2, 23, NULL, 5, '2024-09-16 13:15:40', '2024-09-16 13:15:40'),
(32, 1, 2, 24, NULL, 6, '2024-09-16 13:15:40', '2024-09-16 13:15:40'),
(33, 1, 2, 25, NULL, 7, '2024-09-16 13:15:40', '2024-09-16 13:15:40'),
(34, 1, 2, 26, NULL, 8, '2024-09-16 13:15:40', '2024-09-16 13:15:40'),
(35, 1, 2, 27, NULL, 9, '2024-09-16 13:15:40', '2024-09-16 13:15:40'),
(36, 1, 2, 28, NULL, 10, '2024-09-16 13:15:40', '2024-09-16 13:15:40'),
(37, 1, 3, 2, NULL, 1, '2024-09-16 13:15:58', '2024-09-16 13:15:58'),
(38, 1, 3, 29, NULL, 2, '2024-09-16 13:15:58', '2024-09-16 13:15:58'),
(39, 1, 3, 30, NULL, 3, '2024-09-16 13:15:58', '2024-09-16 13:15:58'),
(40, 1, 3, 31, NULL, 4, '2024-09-16 13:15:58', '2024-09-16 13:15:58'),
(41, 1, 3, 32, NULL, 5, '2024-09-16 13:15:58', '2024-09-16 13:15:58'),
(42, 1, 3, 33, NULL, 6, '2024-09-16 13:15:58', '2024-09-16 13:15:58'),
(43, 1, 3, 34, NULL, 7, '2024-09-16 13:15:58', '2024-09-16 13:15:58'),
(44, 1, 3, 35, NULL, 8, '2024-09-16 13:15:58', '2024-09-16 13:15:58'),
(45, 1, 3, 36, NULL, 9, '2024-09-16 13:15:58', '2024-09-16 13:15:58'),
(46, 1, 3, 37, NULL, 10, '2024-09-16 13:15:58', '2024-09-16 13:15:58'),
(47, 1, 4, 1, NULL, 1, '2024-09-16 13:18:59', '2024-09-16 13:18:59'),
(48, 1, 4, 38, NULL, 2, '2024-09-16 13:18:59', '2024-09-16 13:18:59'),
(49, 1, 4, 39, NULL, 3, '2024-09-16 13:18:59', '2024-09-16 13:18:59'),
(50, 1, 4, 40, NULL, 4, '2024-09-16 13:18:59', '2024-09-16 13:18:59'),
(51, 1, 4, 41, NULL, 5, '2024-09-16 13:18:59', '2024-09-16 13:18:59'),
(52, 1, 6, 6, NULL, 1, '2024-09-16 15:58:16', '2024-09-16 15:58:16'),
(53, 1, 6, 42, NULL, 2, '2024-09-16 15:58:16', '2024-09-16 15:58:16'),
(54, 1, 6, 43, NULL, 3, '2024-09-16 15:58:16', '2024-09-16 15:58:16'),
(55, 1, 6, 44, NULL, 4, '2024-09-16 15:58:16', '2024-09-16 15:58:16'),
(56, 1, 6, 45, NULL, 5, '2024-09-16 15:58:16', '2024-09-16 15:58:16'),
(57, 1, 7, 7, NULL, 1, '2024-09-16 15:58:37', '2024-09-16 15:58:37'),
(58, 1, 7, 46, NULL, 2, '2024-09-16 15:58:37', '2024-09-16 15:58:37'),
(59, 1, 7, 47, NULL, 3, '2024-09-16 15:58:37', '2024-09-16 15:58:37'),
(60, 1, 7, 48, NULL, 4, '2024-09-16 15:58:37', '2024-09-16 15:58:37'),
(61, 1, 7, 49, NULL, 5, '2024-09-16 15:58:37', '2024-09-16 15:58:37'),
(62, 1, 7, 50, NULL, 6, '2024-09-16 15:58:37', '2024-09-16 15:58:37'),
(63, 1, 7, 51, NULL, 7, '2024-09-16 15:58:37', '2024-09-16 15:58:37'),
(64, 1, 7, 52, NULL, 8, '2024-09-16 15:58:37', '2024-09-16 15:58:37'),
(65, 1, 7, 53, NULL, 9, '2024-09-16 15:58:37', '2024-09-16 15:58:37'),
(66, 1, 7, 54, NULL, 10, '2024-09-16 15:58:37', '2024-09-16 15:58:37'),
(67, 1, 8, 8, NULL, 1, '2024-09-16 16:03:54', '2024-09-16 16:03:54'),
(68, 1, 8, 55, NULL, 2, '2024-09-16 16:03:54', '2024-09-16 16:03:54'),
(69, 1, 8, 56, NULL, 3, '2024-09-16 16:03:54', '2024-09-16 16:03:54'),
(70, 1, 8, 57, NULL, 4, '2024-09-16 16:03:54', '2024-09-16 16:03:54'),
(71, 1, 8, 58, NULL, 5, '2024-09-16 16:03:54', '2024-09-16 16:03:54'),
(72, 1, 8, 59, NULL, 6, '2024-09-16 16:03:54', '2024-09-16 16:03:54'),
(73, 1, 8, 60, NULL, 7, '2024-09-16 16:03:54', '2024-09-16 16:03:54'),
(74, 1, 8, 61, NULL, 8, '2024-09-16 16:03:54', '2024-09-16 16:03:54'),
(75, 1, 8, 62, NULL, 9, '2024-09-16 16:03:54', '2024-09-16 16:03:54'),
(76, 1, 8, 63, NULL, 10, '2024-09-16 16:03:54', '2024-09-16 16:03:54'),
(77, 1, 9, 9, NULL, 1, '2024-09-16 16:09:37', '2024-09-16 16:09:37'),
(78, 1, 9, 38, NULL, 2, '2024-09-16 16:09:37', '2024-09-16 16:09:37'),
(79, 1, 9, 39, NULL, 3, '2024-09-16 16:09:37', '2024-09-16 16:09:37'),
(80, 1, 9, 40, NULL, 4, '2024-09-16 16:09:37', '2024-09-16 16:09:37'),
(81, 1, 9, 41, NULL, 5, '2024-09-16 16:09:37', '2024-09-16 16:09:37'),
(82, 1, 11, 11, NULL, 1, '2024-09-16 16:23:33', '2024-09-16 16:23:33'),
(83, 1, 11, 68, NULL, 2, '2024-09-16 16:23:33', '2024-09-16 16:23:33'),
(84, 1, 11, 69, NULL, 3, '2024-09-16 16:23:33', '2024-09-16 16:23:33'),
(85, 1, 11, 70, NULL, 4, '2024-09-16 16:23:33', '2024-09-16 16:23:33'),
(86, 1, 11, 71, NULL, 5, '2024-09-16 16:23:33', '2024-09-16 16:23:33'),
(87, 1, 12, 12, NULL, 1, '2024-09-16 16:34:04', '2024-09-16 16:34:04'),
(88, 1, 12, 72, NULL, 2, '2024-09-16 16:34:04', '2024-09-16 16:34:04'),
(89, 1, 12, 73, NULL, 3, '2024-09-16 16:34:04', '2024-09-16 16:34:04'),
(90, 1, 12, 74, NULL, 4, '2024-09-16 16:34:04', '2024-09-16 16:34:04'),
(91, 1, 12, 75, NULL, 5, '2024-09-16 16:34:04', '2024-09-16 16:34:04'),
(92, 1, 12, 76, NULL, 6, '2024-09-16 16:34:04', '2024-09-16 16:34:04'),
(93, 1, 12, 77, NULL, 7, '2024-09-16 16:34:04', '2024-09-16 16:34:04'),
(94, 1, 12, 78, NULL, 8, '2024-09-16 16:34:04', '2024-09-16 16:34:04'),
(95, 1, 12, 79, NULL, 9, '2024-09-16 16:34:04', '2024-09-16 16:34:04'),
(96, 1, 12, 80, NULL, 10, '2024-09-16 16:34:04', '2024-09-16 16:34:04'),
(97, 1, 13, 13, NULL, 1, '2024-09-16 16:49:40', '2024-09-16 16:49:40'),
(98, 1, 13, 81, NULL, 2, '2024-09-16 16:49:40', '2024-09-16 16:49:40'),
(99, 1, 13, 82, NULL, 3, '2024-09-16 16:49:40', '2024-09-16 16:49:40'),
(100, 1, 13, 83, NULL, 4, '2024-09-16 16:49:40', '2024-09-16 16:49:40'),
(101, 1, 13, 84, NULL, 5, '2024-09-16 16:49:40', '2024-09-16 16:49:40'),
(102, 1, 13, 85, NULL, 6, '2024-09-16 16:49:40', '2024-09-16 16:49:40'),
(103, 1, 13, 86, NULL, 7, '2024-09-16 16:49:40', '2024-09-16 16:49:40'),
(104, 1, 13, 87, NULL, 8, '2024-09-16 16:49:40', '2024-09-16 16:49:40'),
(105, 1, 13, 88, NULL, 9, '2024-09-16 16:49:40', '2024-09-16 16:49:40'),
(106, 1, 13, 89, NULL, 10, '2024-09-16 16:49:40', '2024-09-16 16:49:40'),
(107, 1, 14, 14, NULL, 1, '2024-09-16 16:54:03', '2024-09-16 16:54:03'),
(108, 1, 14, 90, NULL, 2, '2024-09-16 16:54:03', '2024-09-16 16:54:03'),
(109, 1, 14, 91, NULL, 3, '2024-09-16 16:54:03', '2024-09-16 16:54:03'),
(110, 1, 14, 92, NULL, 4, '2024-09-16 16:54:03', '2024-09-16 16:54:03'),
(111, 1, 14, 93, NULL, 5, '2024-09-16 16:54:03', '2024-09-16 16:54:03'),
(114, 1, 16, NULL, 1, 1, '2024-09-17 14:38:54', '2024-09-17 14:38:54'),
(115, 1, 16, NULL, 2, 2, '2024-09-17 14:38:54', '2024-09-17 14:38:54'),
(116, 1, 16, NULL, 3, 3, '2024-09-17 14:38:54', '2024-09-17 14:38:54'),
(117, 1, 16, NULL, 4, 4, '2024-09-17 14:38:54', '2024-09-17 14:38:54'),
(118, 1, 16, NULL, 5, 5, '2024-09-17 14:38:54', '2024-09-17 14:38:54'),
(119, 1, 16, NULL, 6, 6, '2024-09-17 14:38:54', '2024-09-17 14:38:54'),
(150, 1, 17, NULL, 7, 1, '2024-09-18 07:27:31', '2024-09-18 07:27:31'),
(151, 1, 17, NULL, 13, 2, '2024-09-18 07:27:31', '2024-09-18 07:27:31'),
(152, 1, 17, NULL, 18, 3, '2024-09-18 07:27:31', '2024-09-18 07:27:31'),
(153, 1, 17, NULL, 8, 4, '2024-09-18 07:27:31', '2024-09-18 07:27:31'),
(154, 1, 17, NULL, 14, 5, '2024-09-18 07:27:31', '2024-09-18 07:27:31'),
(155, 1, 17, NULL, 17, 6, '2024-09-18 07:27:31', '2024-09-18 07:27:31'),
(156, 1, 17, NULL, 9, 7, '2024-09-18 07:27:31', '2024-09-18 07:27:31'),
(157, 1, 17, NULL, 15, 8, '2024-09-18 07:27:31', '2024-09-18 07:27:31'),
(158, 1, 17, NULL, 16, 9, '2024-09-18 07:27:31', '2024-09-18 07:27:31'),
(159, 1, 17, NULL, 10, 10, '2024-09-18 07:27:31', '2024-09-18 07:27:31'),
(160, 1, 17, NULL, 19, 11, '2024-09-18 07:27:31', '2024-09-18 07:27:31'),
(161, 1, 17, NULL, 11, 12, '2024-09-18 07:27:31', '2024-09-18 07:27:31'),
(162, 1, 17, NULL, 20, 13, '2024-09-18 07:27:31', '2024-09-18 07:27:31'),
(163, 1, 17, NULL, 12, 14, '2024-09-18 07:27:31', '2024-09-18 07:27:31'),
(164, 1, 17, NULL, 21, 15, '2024-09-18 07:27:31', '2024-09-18 07:27:31'),
(165, 1, 17, NULL, 22, 16, '2024-09-18 07:27:31', '2024-09-18 07:27:31'),
(166, 1, 17, NULL, 23, 17, '2024-09-18 07:27:31', '2024-09-18 07:27:31'),
(167, 1, 17, NULL, 24, 18, '2024-09-18 07:27:31', '2024-09-18 07:27:31'),
(168, 1, 17, NULL, 25, 19, '2024-09-18 07:27:31', '2024-09-18 07:27:31'),
(169, 1, 17, NULL, 26, 20, '2024-09-18 07:27:31', '2024-09-18 07:27:31'),
(170, 1, 17, NULL, 27, 21, '2024-09-18 07:27:31', '2024-09-18 07:27:31'),
(171, 1, 17, NULL, 28, 22, '2024-09-18 07:27:31', '2024-09-18 07:27:31'),
(172, 1, 17, NULL, 29, 23, '2024-09-18 07:27:31', '2024-09-18 07:27:31'),
(173, 1, 19, NULL, 36, 1, '2024-09-18 11:11:08', '2024-09-18 11:11:08'),
(174, 1, 19, NULL, 37, 2, '2024-09-18 11:11:08', '2024-09-18 11:11:08'),
(175, 1, 19, NULL, 38, 3, '2024-09-18 11:11:08', '2024-09-18 11:11:08'),
(176, 1, 19, NULL, 39, 4, '2024-09-18 11:11:08', '2024-09-18 11:11:08'),
(177, 1, 19, NULL, 40, 5, '2024-09-18 11:11:08', '2024-09-18 11:11:08'),
(178, 1, 20, NULL, 42, 1, '2024-09-27 18:40:52', '2024-09-27 18:40:52'),
(179, 1, 21, NULL, 43, 1, '2024-09-27 19:12:27', '2024-09-27 19:12:27'),
(180, 1, 21, NULL, 44, 2, '2024-09-27 19:12:27', '2024-09-27 19:12:27'),
(181, 1, 21, NULL, 45, 3, '2024-09-27 19:12:27', '2024-09-27 19:12:27'),
(182, 1, 21, NULL, 46, 4, '2024-09-27 19:12:27', '2024-09-27 19:12:27'),
(183, 1, 21, NULL, 47, 5, '2024-09-27 19:12:27', '2024-09-27 19:12:27'),
(184, 1, 21, NULL, 48, 6, '2024-09-27 19:12:27', '2024-09-27 19:12:27'),
(186, 1, 23, NULL, 50, 1, '2024-10-29 17:01:27', '2024-10-29 17:01:27'),
(187, 1, 23, NULL, 51, 2, '2024-10-29 17:01:27', '2024-10-29 17:01:27'),
(188, 1, 23, NULL, 52, 3, '2024-10-29 17:01:27', '2024-10-29 17:01:27'),
(189, 1, 22, NULL, 49, 1, '2024-11-02 15:26:45', '2024-11-02 15:26:45'),
(190, 1, 22, NULL, 53, 2, '2024-11-02 15:26:45', '2024-11-02 15:26:45'),
(191, 1, 22, NULL, 54, 3, '2024-11-02 15:26:45', '2024-11-02 15:26:45'),
(192, 1, 22, NULL, 55, 4, '2024-11-02 15:26:45', '2024-11-02 15:26:45'),
(193, 1, 22, NULL, 56, 5, '2024-11-02 15:26:45', '2024-11-02 15:26:45'),
(194, 1, 22, NULL, 57, 6, '2024-11-02 15:26:45', '2024-11-02 15:26:45'),
(195, 1, 22, NULL, 58, 7, '2024-11-02 15:26:45', '2024-11-02 15:26:45'),
(196, 1, 22, NULL, 59, 8, '2024-11-02 15:26:45', '2024-11-02 15:26:45'),
(197, 1, 22, NULL, 60, 9, '2024-11-02 15:26:45', '2024-11-02 15:26:45'),
(198, 1, 22, NULL, 61, 10, '2024-11-02 15:26:45', '2024-11-02 15:26:45'),
(199, 1, 24, NULL, 62, 1, '2024-11-02 16:21:07', '2024-11-02 16:21:07'),
(200, 1, 24, NULL, 63, 2, '2024-11-02 16:21:07', '2024-11-02 16:21:07'),
(201, 1, 24, NULL, 64, 3, '2024-11-02 16:21:07', '2024-11-02 16:21:07'),
(202, 1, 24, NULL, 65, 4, '2024-11-02 16:21:07', '2024-11-02 16:21:07'),
(203, 1, 24, NULL, 66, 5, '2024-11-02 16:21:07', '2024-11-02 16:21:07'),
(204, 1, 25, NULL, 67, 1, '2024-11-02 16:21:48', '2024-11-02 16:21:48'),
(205, 1, 25, NULL, 68, 2, '2024-11-02 16:21:48', '2024-11-02 16:21:48'),
(206, 1, 25, NULL, 69, 3, '2024-11-02 16:21:48', '2024-11-02 16:21:48'),
(207, 1, 25, NULL, 70, 4, '2024-11-02 16:21:48', '2024-11-02 16:21:48'),
(208, 1, 25, NULL, 71, 5, '2024-11-02 16:21:48', '2024-11-02 16:21:48'),
(209, 1, 26, NULL, 72, 1, '2024-11-02 16:22:17', '2024-11-02 16:22:17'),
(210, 1, 26, NULL, 73, 2, '2024-11-02 16:22:17', '2024-11-02 16:22:17'),
(211, 1, 26, NULL, 74, 3, '2024-11-02 16:22:17', '2024-11-02 16:22:17'),
(212, 1, 26, NULL, 75, 4, '2024-11-02 16:22:17', '2024-11-02 16:22:17'),
(213, 1, 26, NULL, 76, 5, '2024-11-02 16:22:17', '2024-11-02 16:22:17'),
(216, 1, 28, NULL, 79, 1, '2024-11-03 22:16:50', '2024-11-03 22:16:50'),
(217, 1, 29, NULL, 81, 1, '2024-11-06 03:57:42', '2024-11-06 03:57:42'),
(223, 1, 33, NULL, 87, 1, '2024-11-08 19:24:45', '2024-11-08 19:24:45'),
(224, 1, 34, NULL, 88, 1, '2024-11-08 19:24:57', '2024-11-08 19:24:57'),
(227, 1, 36, NULL, 91, 1, '2024-11-09 00:57:27', '2024-11-09 00:57:27'),
(228, 1, 36, NULL, 92, 2, '2024-11-09 00:57:27', '2024-11-09 00:57:27'),
(238, 1, 32, NULL, 85, 1, '2025-03-15 19:13:19', '2025-03-15 19:13:19'),
(239, 1, 32, NULL, 86, 2, '2025-03-15 19:13:19', '2025-03-15 19:13:19'),
(240, 1, 32, NULL, 95, 3, '2025-03-15 19:13:19', '2025-03-15 19:13:19'),
(241, 1, 30, NULL, 96, 1, '2025-03-15 19:44:33', '2025-03-15 19:44:33'),
(242, 1, 30, NULL, 82, 2, '2025-03-15 19:44:33', '2025-03-15 19:44:33'),
(243, 1, 35, NULL, 98, 1, '2025-04-30 18:35:20', '2025-04-30 18:35:20'),
(244, 1, 35, NULL, 89, 2, '2025-04-30 18:35:20', '2025-04-30 18:35:20'),
(245, 1, 35, NULL, 90, 3, '2025-04-30 18:35:20', '2025-04-30 18:35:20'),
(246, 1, 35, NULL, 93, 4, '2025-04-30 18:35:20', '2025-04-30 18:35:20'),
(247, 1, 27, NULL, 77, 1, '2025-05-01 21:48:05', '2025-05-01 21:48:05'),
(248, 1, 27, NULL, 78, 2, '2025-05-01 21:48:05', '2025-05-01 21:48:05'),
(249, 1, 27, NULL, 101, 3, '2025-05-01 21:48:05', '2025-05-01 21:48:05'),
(250, 1, 37, NULL, 102, 1, '2025-05-07 23:03:24', '2025-05-07 23:03:24'),
(251, 1, 38, NULL, 103, 1, '2025-05-07 23:16:43', '2025-05-07 23:16:43');

-- --------------------------------------------------------

--
-- Table structure for table `unit_fires`
--

CREATE TABLE `unit_fires` (
  `id` int(11) NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `unit_id` int(10) UNSIGNED DEFAULT NULL,
  `reading_persantage` int(10) UNSIGNED DEFAULT NULL,
  `writing_percentage` int(10) UNSIGNED DEFAULT NULL,
  `listening_percentage` int(10) UNSIGNED DEFAULT NULL,
  `speaking_percentage` int(10) UNSIGNED DEFAULT NULL,
  `all_persentage` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `unit_fires`
--

INSERT INTO `unit_fires` (`id`, `user_id`, `unit_id`, `reading_persantage`, `writing_percentage`, `listening_percentage`, `speaking_percentage`, `all_persentage`) VALUES
(1, 1, 1, 0, 100, 0, 0, 25),
(2, 1, 9, 0, 0, 0, 0, 0),
(3, 1, 7, 0, 0, 0, 0, 0),
(4, 1, 16, 0, 0, 0, 0, 0),
(5, 1, 17, 0, 0, 0, 0, 0),
(6, 1, 19, 75, 0, 75, 0, 38),
(7, 1, 21, NULL, NULL, NULL, NULL, NULL),
(8, 1, 20, 25, 0, 0, 0, 6),
(9, 1, 23, 0, 0, 0, 0, 0),
(10, 1, 22, 0, 0, 0, 0, 0),
(11, 1, 24, NULL, NULL, NULL, NULL, NULL),
(12, 1, 25, NULL, NULL, NULL, NULL, NULL),
(13, 1, 26, NULL, NULL, NULL, NULL, NULL),
(14, 1, 27, NULL, NULL, NULL, NULL, NULL),
(15, 1, 29, 0, 100, 0, 0, 25),
(16, 1, 32, 50, 100, 0, 0, 38),
(17, 1, 31, 0, 100, 0, 0, 25),
(18, 1, 30, 50, 100, 0, 0, 38),
(19, 1, 34, NULL, NULL, NULL, NULL, NULL),
(20, 1, 36, 100, 33, 0, 0, 33),
(21, 1, 35, 0, 100, 0, 0, 25);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `email` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `password` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `name` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `approximate_age` tinyint(3) UNSIGNED DEFAULT NULL COMMENT 'Approximate age to reduce sensitive data. Null if not updated.',
  `google_id` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `google_status` enum('1','0') COLLATE latin1_general_ci DEFAULT '0',
  `fb_id` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `fb_status` enum('1','0') COLLATE latin1_general_ci DEFAULT '0',
  `apple_id` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `apple_status` enum('1','0') COLLATE latin1_general_ci DEFAULT NULL,
  `clever_id` varchar(255) COLLATE latin1_general_ci DEFAULT NULL COMMENT ' 	User''s Clever ID if they use Clever to sign in ',
  `role_id` int(11) UNSIGNED DEFAULT '3',
  `learningspeed_id` int(11) UNSIGNED DEFAULT NULL,
  `learningpath_id` int(11) UNSIGNED DEFAULT NULL,
  `agreements_accepted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'User accepted agreements',
  `last_logged` datetime DEFAULT NULL,
  `is_active` enum('1','0') COLLATE latin1_general_ci DEFAULT '1',
  `complete_findfriend_page` enum('1','0') COLLATE latin1_general_ci DEFAULT '0',
  `is_delete` enum('1','0') COLLATE latin1_general_ci DEFAULT '0',
  `registered` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `name`, `dob`, `approximate_age`, `google_id`, `google_status`, `fb_id`, `fb_status`, `apple_id`, `apple_status`, `clever_id`, `role_id`, `learningspeed_id`, `learningpath_id`, `agreements_accepted`, `last_logged`, `is_active`, `complete_findfriend_page`, `is_delete`, `registered`, `created`, `modified`) VALUES
(1, 'admin@demo.org', '$2y$10$b1egD4SBTiLdqzZMEPMhDeNSoSUu2kbCftIxoTompzfNUhlIp6xbi', 'admin', '2000-01-01', 25, NULL, '0', NULL, '0', NULL, NULL, NULL, 1, 1, 1, 1, '2025-07-18 21:54:15', '1', '0', '0', '2024-09-14 19:33:48', '2024-09-14 19:33:48', '2025-07-18 21:54:15'),
(2, 'user@demo.org', '$2y$10$dE2U.5tWU/QNv1DL97j2vuwvm/ta7LZb5p5B29nWgcJxp/K4/s/ZS', 'user', '2000-12-12', 24, NULL, '0', NULL, '0', NULL, NULL, NULL, 3, 1, 1, 1, '2025-07-10 22:24:17', '1', '0', '0', '2024-10-09 16:56:18', '2024-10-09 16:56:18', '2025-07-10 22:24:27'),
(3, 'teacher@demo.org', '$2y$10$LsH3RfE9d7y0PQGOQJzgCuGwXYHWnkWVcycp9kd8Bk/0SFvRUbzqq', 'teacher', '1985-01-01', 40, NULL, '0', NULL, '0', NULL, NULL, NULL, 2, 1, 1, 1, '2025-07-08 17:54:34', '1', '0', '0', '2025-07-08 17:53:33', '2025-07-08 17:53:33', '2025-07-08 17:55:47');

-- --------------------------------------------------------

--
-- Table structure for table `user_activities`
--

CREATE TABLE `user_activities` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `activity_type` enum('lesson','forumpost','exercise','test','unit','level','card','badge','review') DEFAULT NULL,
  `type` enum('wrong','right') DEFAULT NULL,
  `level_id` int(11) UNSIGNED DEFAULT NULL,
  `unit_id` int(11) UNSIGNED DEFAULT NULL,
  `exercise_id` int(11) UNSIGNED DEFAULT NULL,
  `exercise_type` varchar(255) DEFAULT NULL,
  `lesson_id` int(11) UNSIGNED DEFAULT NULL,
  `lessonframe_id` int(11) UNSIGNED DEFAULT NULL,
  `card_id` int(11) UNSIGNED DEFAULT NULL,
  `user_unit_activity_id` int(11) UNSIGNED DEFAULT NULL,
  `test_id` int(11) UNSIGNED DEFAULT NULL,
  `badge_id` int(11) UNSIGNED DEFAULT NULL,
  `forumpost_id` int(11) UNSIGNED DEFAULT NULL,
  `path_score` float(11,2) UNSIGNED ZEROFILL NOT NULL DEFAULT '00000000.00',
  `review_score` float(11,2) UNSIGNED ZEROFILL NOT NULL DEFAULT '00000000.00',
  `social_score` float(11,2) UNSIGNED ZEROFILL NOT NULL DEFAULT '00000000.00',
  `reading_score` float(11,2) UNSIGNED ZEROFILL NOT NULL DEFAULT '00000000.00',
  `writing_score` float(11,2) UNSIGNED ZEROFILL NOT NULL DEFAULT '00000000.00',
  `speaking_score` float(11,2) UNSIGNED ZEROFILL NOT NULL DEFAULT '00000000.00',
  `listening_score` float(11,2) UNSIGNED ZEROFILL NOT NULL DEFAULT '00000000.00',
  `is_temporary` enum('Y','N') NOT NULL DEFAULT 'Y',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

--
-- Dumping data for table `user_activities`
--

INSERT INTO `user_activities` (`id`, `user_id`, `activity_type`, `type`, `level_id`, `unit_id`, `exercise_id`, `exercise_type`, `lesson_id`, `lessonframe_id`, `card_id`, `user_unit_activity_id`, `test_id`, `badge_id`, `forumpost_id`, `path_score`, `review_score`, `social_score`, `reading_score`, `writing_score`, `speaking_score`, `listening_score`, `is_temporary`, `created`, `modified`) VALUES
(10, 1, 'lesson', NULL, 2, 1, NULL, NULL, 5, 30, NULL, 1, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-15 16:26:36', '2024-09-15 16:26:36'),
(11, 1, 'lesson', NULL, 2, 1, NULL, NULL, 5, 37, NULL, 1, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-15 16:26:37', '2024-09-15 16:26:37'),
(12, 1, 'lesson', NULL, 2, 1, NULL, NULL, 5, 38, NULL, 1, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-15 16:26:38', '2024-09-15 16:26:38'),
(13, 1, 'lesson', NULL, 2, 1, NULL, NULL, 5, 39, NULL, 1, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-15 16:26:39', '2024-09-15 16:26:39'),
(14, 1, 'lesson', NULL, 2, 1, NULL, NULL, 5, 40, NULL, 1, NULL, NULL, NULL, 00000005.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-15 16:26:39', '2024-09-15 16:26:39'),
(15, 1, NULL, NULL, 2, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, 00000015.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-15 16:26:40', '2024-09-15 16:26:40'),
(16, 1, 'lesson', NULL, 4, 9, NULL, NULL, 9, 69, NULL, 2, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-16 07:52:07', '2024-09-16 07:52:07'),
(17, 1, 'lesson', NULL, 4, 9, NULL, NULL, 9, 70, NULL, 2, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-16 07:52:16', '2024-09-16 07:52:16'),
(18, 1, 'lesson', NULL, 4, 9, NULL, NULL, 9, 71, NULL, 2, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-16 07:52:22', '2024-09-16 07:52:22'),
(19, 1, 'lesson', NULL, 4, 9, NULL, NULL, 9, 72, NULL, 2, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-16 07:52:27', '2024-09-16 07:52:27'),
(20, 1, 'lesson', NULL, 4, 7, NULL, NULL, 7, 49, NULL, 3, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-16 12:24:42', '2024-09-16 12:24:42'),
(21, 1, 'lesson', NULL, 4, 7, NULL, NULL, 7, 50, NULL, 3, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-16 12:24:49', '2024-09-16 12:24:49'),
(22, 1, 'lesson', NULL, 4, 7, NULL, NULL, 7, 51, NULL, 3, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-16 12:24:57', '2024-09-16 12:24:57'),
(23, 1, 'lesson', NULL, 4, 7, NULL, NULL, 7, 52, NULL, 3, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-16 12:24:59', '2024-09-16 12:24:59'),
(24, 1, 'lesson', NULL, 4, 7, NULL, NULL, 7, 53, NULL, 3, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-16 12:25:02', '2024-09-16 12:25:02'),
(25, 1, 'lesson', NULL, 4, 7, NULL, NULL, 7, 54, NULL, 3, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-16 12:25:04', '2024-09-16 12:25:04'),
(26, 1, 'lesson', NULL, 4, 7, NULL, NULL, 7, 55, NULL, 3, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-16 12:25:07', '2024-09-16 12:25:07'),
(27, 1, 'lesson', NULL, 4, 7, NULL, NULL, 7, 56, NULL, 3, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-16 12:25:09', '2024-09-16 12:25:09'),
(28, 1, 'lesson', NULL, 4, 7, NULL, NULL, 7, 57, NULL, 3, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-16 12:25:10', '2024-09-16 12:25:10'),
(29, 1, 'lesson', NULL, 4, 7, NULL, NULL, 7, 58, NULL, 3, NULL, NULL, NULL, 00000005.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-16 12:25:13', '2024-09-16 12:25:13'),
(30, 1, NULL, NULL, 4, 7, NULL, NULL, NULL, NULL, NULL, 3, NULL, NULL, NULL, 00000015.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-16 12:25:14', '2024-09-16 12:25:14'),
(31, 1, 'exercise', 'right', 3, 16, 1, 'multiple-choice', NULL, NULL, 1, 4, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000005.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-16 18:10:07', '2024-09-16 18:10:07'),
(32, 1, 'exercise', 'wrong', 3, 16, 2, 'multiple-choice', NULL, NULL, 1, 4, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-16 18:10:35', '2024-09-16 18:10:35'),
(33, 1, 'exercise', 'right', 3, 16, 2, 'multiple-choice', NULL, NULL, 1, 4, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-16 18:10:42', '2024-09-16 18:10:42'),
(34, 1, 'exercise', 'wrong', 3, 17, 7, 'multiple-choice', NULL, NULL, 2, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-17 15:49:27', '2024-09-17 15:49:27'),
(35, 1, 'exercise', 'right', 3, 17, 7, 'multiple-choice', NULL, NULL, 2, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000002.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-17 15:49:31', '2024-09-17 15:49:31'),
(36, 1, 'exercise', 'right', 3, 17, 8, 'multiple-choice', NULL, NULL, 3, 5, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000001.00, 00000000.00, 00000000.00, 00000002.00, 'Y', '2024-09-17 15:49:42', '2024-09-17 15:49:42'),
(37, 1, 'exercise', 'right', 3, 19, 36, 'multiple-choice', NULL, NULL, 2, 6, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000004.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-18 11:18:52', '2024-09-18 11:18:52'),
(38, 1, 'exercise', 'right', 3, 19, 37, 'multiple-choice', NULL, NULL, 4, 6, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000003.00, 'Y', '2024-09-18 11:19:08', '2024-09-18 11:19:08'),
(39, 1, 'exercise', 'wrong', 3, 19, 38, 'multiple-choice', NULL, NULL, 1, 6, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-18 11:28:15', '2024-09-18 11:28:15'),
(40, 1, 'exercise', 'wrong', 3, 19, 39, 'multiple-choice', NULL, NULL, 3, 6, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-25 17:54:30', '2024-09-25 17:54:30'),
(41, 1, 'exercise', 'wrong', 3, 19, 39, 'multiple-choice', NULL, NULL, 3, 6, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-26 22:46:22', '2024-09-26 22:46:22'),
(42, 1, 'exercise', 'wrong', 3, 19, 40, 'multiple-choice', NULL, NULL, 2, 6, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-26 22:47:49', '2024-09-26 22:47:49'),
(43, 1, 'exercise', 'wrong', 3, 19, 39, 'multiple-choice', NULL, NULL, 3, 6, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-26 22:48:06', '2024-09-26 22:48:06'),
(44, 1, 'exercise', 'wrong', 3, 19, 40, 'multiple-choice', NULL, NULL, 2, 6, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-26 22:48:50', '2024-09-26 22:48:50'),
(45, 1, 'exercise', 'right', 3, 19, 38, 'multiple-choice', NULL, NULL, 1, 6, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-26 22:48:53', '2024-09-26 22:48:53'),
(46, 1, 'exercise', 'wrong', 3, 19, 39, 'multiple-choice', NULL, NULL, 3, 6, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-26 22:48:56', '2024-09-26 22:48:56'),
(47, 1, 'exercise', 'wrong', 3, 19, 40, 'multiple-choice', NULL, NULL, 2, 6, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-26 22:49:03', '2024-09-26 22:49:03'),
(48, 1, 'exercise', 'wrong', 3, 19, 39, 'multiple-choice', NULL, NULL, 3, 6, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-26 22:49:08', '2024-09-26 22:49:08'),
(49, 1, 'exercise', 'right', 3, 19, 40, 'multiple-choice', NULL, NULL, 2, 6, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000001.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-26 22:49:11', '2024-09-26 22:49:11'),
(50, 1, 'exercise', 'right', 3, 19, 39, 'multiple-choice', NULL, NULL, 3, 6, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-26 22:49:16', '2024-09-26 22:49:16'),
(51, 1, 'exercise', 'wrong', 3, 19, 36, 'multiple-choice', NULL, NULL, 2, 7, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-26 22:49:24', '2024-09-26 22:49:24'),
(52, 1, 'exercise', 'wrong', 3, 19, 38, 'multiple-choice', NULL, NULL, 1, 7, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-26 22:49:34', '2024-09-26 22:49:34'),
(53, 1, 'exercise', 'wrong', 3, 19, 38, 'multiple-choice', NULL, NULL, 1, 7, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-26 22:51:38', '2024-09-26 22:51:38'),
(54, 1, 'exercise', 'right', 3, 19, 37, 'multiple-choice', NULL, NULL, 4, 7, NULL, NULL, NULL, 00000002.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000002.00, 'Y', '2024-09-26 22:51:51', '2024-09-26 22:51:51'),
(55, 1, 'exercise', 'wrong', 3, 19, 37, 'multiple-choice', NULL, NULL, 4, 7, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-26 22:51:57', '2024-09-26 22:51:57'),
(56, 1, 'exercise', 'wrong', 3, 19, 37, 'multiple-choice', NULL, NULL, 4, 7, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-26 22:58:10', '2024-09-26 22:58:10'),
(57, 1, 'exercise', 'wrong', 3, 19, 39, 'multiple-choice', NULL, NULL, 3, 7, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-26 22:58:19', '2024-09-26 22:58:19'),
(58, 1, 'exercise', 'wrong', 3, 19, 40, 'multiple-choice', NULL, NULL, 2, 7, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-26 22:58:26', '2024-09-26 22:58:26'),
(59, 1, 'exercise', 'wrong', 3, 17, 7, 'multiple-choice', NULL, NULL, 2, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 18:09:40', '2024-09-27 18:09:40'),
(60, 1, 'exercise', 'wrong', 3, 17, 13, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 18:09:42', '2024-09-27 18:09:42'),
(61, 1, 'exercise', 'wrong', 3, 17, 18, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 18:09:46', '2024-09-27 18:09:46'),
(62, 1, 'exercise', 'right', 3, 17, 7, 'multiple-choice', NULL, NULL, 2, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000001.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 18:20:49', '2024-09-27 18:20:49'),
(63, 1, 'exercise', 'wrong', 3, 17, 9, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 18:26:32', '2024-09-27 18:26:32'),
(64, 1, 'exercise', 'right', 3, 17, 15, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 18:26:36', '2024-09-27 18:26:36'),
(65, 1, 'exercise', 'wrong', 3, 17, 16, 'multiple-choice', NULL, NULL, 3, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 18:26:39', '2024-09-27 18:26:39'),
(66, 1, 'exercise', 'right', 3, 17, 9, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 18:28:45', '2024-09-27 18:28:45'),
(67, 1, 'exercise', 'wrong', 3, 17, 16, 'multiple-choice', NULL, NULL, 3, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 18:29:32', '2024-09-27 18:29:32'),
(68, 1, 'exercise', 'wrong', 3, 17, 10, 'multiple-choice', NULL, NULL, 3, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 18:29:40', '2024-09-27 18:29:40'),
(69, 1, 'exercise', 'wrong', 3, 17, 19, 'multiple-choice', NULL, NULL, 2, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 18:29:45', '2024-09-27 18:29:45'),
(70, 1, 'exercise', 'right', 3, 17, 11, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000003.00, 'Y', '2024-09-27 18:29:54', '2024-09-27 18:29:54'),
(71, 1, 'exercise', 'wrong', 3, 17, 20, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 18:29:57', '2024-09-27 18:29:57'),
(72, 1, 'exercise', 'wrong', 3, 17, 12, 'multiple-choice', NULL, NULL, 2, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 18:30:00', '2024-09-27 18:30:00'),
(73, 1, 'exercise', 'wrong', 3, 17, 21, 'multiple-choice', NULL, NULL, 3, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 18:30:04', '2024-09-27 18:30:04'),
(74, 1, 'exercise', 'right', 3, 17, 22, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000002.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 18:30:06', '2024-09-27 18:30:06'),
(75, 1, 'exercise', 'right', 3, 17, 23, 'multiple-choice', NULL, NULL, 3, 5, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 18:30:08', '2024-09-27 18:30:08'),
(76, 1, 'exercise', 'right', 3, 17, 24, 'multiple-choice', NULL, NULL, 2, 5, NULL, NULL, NULL, 00000002.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 18:30:11', '2024-09-27 18:30:11'),
(77, 1, 'exercise', 'right', 3, 17, 25, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000002.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 18:30:14', '2024-09-27 18:30:14'),
(78, 1, 'exercise', 'right', 3, 17, 26, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000002.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000003.00, 'Y', '2024-09-27 18:30:18', '2024-09-27 18:30:18'),
(79, 1, 'exercise', 'wrong', 3, 17, 11, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 18:32:22', '2024-09-27 18:32:22'),
(80, 1, 'exercise', 'wrong', 3, 17, 29, 'multiple-choice', NULL, NULL, 2, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 18:32:37', '2024-09-27 18:32:37'),
(81, 1, 'exercise', 'wrong', 3, 17, 28, 'multiple-choice', NULL, NULL, 3, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 18:32:54', '2024-09-27 18:32:54'),
(82, 1, 'exercise', 'wrong', 3, 17, 26, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 18:34:08', '2024-09-27 18:34:08'),
(83, 1, 'exercise', 'right', 6, 21, 43, 'match-the-pair', NULL, NULL, 1, 8, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000004.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 19:12:44', '2024-09-27 19:12:44'),
(84, 1, 'exercise', 'right', 6, 21, 44, 'match-the-pair', NULL, NULL, 2, 8, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 19:16:16', '2024-09-27 19:16:16'),
(85, 1, 'exercise', 'wrong', 6, 21, 44, 'match-the-pair', NULL, NULL, 3, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 19:16:18', '2024-09-27 19:16:18'),
(86, 1, 'exercise', 'wrong', 6, 21, 44, 'match-the-pair', NULL, NULL, 1, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 19:16:22', '2024-09-27 19:16:22'),
(87, 1, 'exercise', 'wrong', 6, 21, 44, 'match-the-pair', NULL, NULL, 4, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 19:16:27', '2024-09-27 19:16:27'),
(88, 1, 'exercise', 'wrong', 6, 21, 45, 'match-the-pair', NULL, NULL, 4, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 19:18:11', '2024-09-27 19:18:11'),
(89, 1, 'exercise', 'right', 6, 21, 45, 'match-the-pair', NULL, NULL, 2, 8, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 19:18:22', '2024-09-27 19:18:22'),
(90, 1, 'exercise', 'wrong', 6, 21, 45, 'match-the-pair', NULL, NULL, 3, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 19:20:04', '2024-09-27 19:20:04'),
(91, 1, 'exercise', 'wrong', 6, 21, 45, 'match-the-pair', NULL, NULL, 3, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 19:20:05', '2024-09-27 19:20:05'),
(92, 1, 'exercise', 'right', 3, 17, 7, 'multiple-choice', NULL, NULL, 2, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000001.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 19:33:12', '2024-09-27 19:33:12'),
(93, 1, 'exercise', 'right', 3, 17, 13, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000003.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 19:33:14', '2024-09-27 19:33:14'),
(94, 1, 'exercise', 'right', 3, 17, 18, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000002.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 19:33:16', '2024-09-27 19:33:16'),
(95, 1, 'exercise', 'right', 3, 17, 8, 'multiple-choice', NULL, NULL, 3, 5, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000001.00, 00000000.00, 00000000.00, 00000001.00, 'Y', '2024-09-27 19:33:17', '2024-09-27 19:33:17'),
(96, 1, 'exercise', 'wrong', 3, 17, 14, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 19:33:19', '2024-09-27 19:33:19'),
(97, 1, 'exercise', 'wrong', 6, 21, 46, 'match-the-pair', NULL, NULL, 4, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 19:44:32', '2024-09-27 19:44:32'),
(98, 1, 'exercise', 'wrong', 6, 21, 46, 'match-the-pair', NULL, NULL, 4, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-09-27 20:10:17', '2024-09-27 20:10:17'),
(99, 1, 'exercise', 'wrong', 6, 20, 42, 'match-the-pair', NULL, NULL, 3, 9, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-10 23:37:23', '2024-10-10 23:37:23'),
(100, 1, 'exercise', 'right', 6, 20, 42, 'match-the-pair', NULL, NULL, 1, 9, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000004.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-10 23:37:44', '2024-10-10 23:37:44'),
(101, 1, 'exercise', 'right', 6, 20, 42, 'match-the-pair', NULL, NULL, 2, 9, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000004.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-10 23:37:51', '2024-10-10 23:37:51'),
(102, 1, 'exercise', 'right', 6, 20, 42, 'match-the-pair', NULL, NULL, 4, 9, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000004.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-10 23:37:54', '2024-10-10 23:37:54'),
(103, 1, 'exercise', 'right', 6, 20, 42, 'match-the-pair', NULL, NULL, 2, 9, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-10 23:38:00', '2024-10-10 23:38:00'),
(104, 1, 'exercise', 'right', 6, 20, 42, 'match-the-pair', NULL, NULL, 1, 9, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-10 23:38:03', '2024-10-10 23:38:03'),
(105, 1, 'exercise', 'right', 6, 20, 42, 'match-the-pair', NULL, NULL, 4, 9, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-10 23:38:07', '2024-10-10 23:38:07'),
(106, 1, 'exercise', 'right', 6, 20, 42, 'match-the-pair', NULL, NULL, 3, 9, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000002.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-10 23:38:11', '2024-10-10 23:38:11'),
(107, 1, 'review', 'wrong', 6, 20, NULL, 'anagram', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-10 23:39:09', '2024-10-10 23:39:09'),
(108, 1, 'review', 'right', 3, 19, NULL, 'anagram', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000007.00, 00000000.00, 00000000.00, 'Y', '2024-10-11 19:28:45', '2024-10-11 19:28:45'),
(109, 1, 'review', 'right', 3, 19, NULL, 'multiple-choice', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000003.00, 'Y', '2024-10-11 19:28:57', '2024-10-11 19:28:57'),
(110, 1, 'review', 'wrong', 3, 19, NULL, 'multiple-choice', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-11 20:56:31', '2024-10-11 20:56:31'),
(111, 1, 'review', 'right', 3, 19, NULL, 'multiple-choice', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000003.00, 'Y', '2024-10-11 20:56:42', '2024-10-11 20:56:42'),
(112, 1, 'review', 'wrong', 6, 20, NULL, 'multiple-choice', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-11 21:03:59', '2024-10-11 21:03:59'),
(113, 1, 'review', 'right', 6, 20, NULL, 'match-the-pair', NULL, NULL, 3, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000003.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-11 21:06:02', '2024-10-11 21:06:02'),
(114, 1, 'review', 'right', 6, 20, NULL, 'match-the-pair', NULL, NULL, 3, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000004.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-11 21:06:27', '2024-10-11 21:06:27'),
(115, 1, 'review', 'right', 6, 20, NULL, 'anagram', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000007.00, 00000000.00, 00000000.00, 'Y', '2024-10-14 17:51:05', '2024-10-14 17:51:05'),
(116, 1, 'review', 'wrong', 6, 20, NULL, 'match-the-pair', NULL, NULL, 3, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-14 21:27:20', '2024-10-14 21:27:20'),
(117, 1, 'review', 'right', 6, 20, NULL, 'match-the-pair', NULL, NULL, 2, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000003.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-14 21:27:22', '2024-10-14 21:27:22'),
(118, 1, 'review', 'right', 6, 20, NULL, 'match-the-pair', NULL, NULL, 1, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000003.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-14 21:27:24', '2024-10-14 21:27:24'),
(119, 1, 'review', 'right', 6, 20, NULL, 'match-the-pair', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000003.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-14 21:27:26', '2024-10-14 21:27:26'),
(120, 1, 'review', 'wrong', 6, 20, NULL, 'multiple-choice', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-14 21:33:05', '2024-10-14 21:33:05'),
(121, 1, 'review', 'right', 6, 20, NULL, 'match-the-pair', NULL, NULL, 3, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000003.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-14 21:33:10', '2024-10-14 21:33:10'),
(122, 1, 'review', 'right', 6, 20, NULL, 'match-the-pair', NULL, NULL, 2, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000003.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-14 21:33:13', '2024-10-14 21:33:13'),
(123, 1, 'review', 'right', 6, 20, NULL, 'match-the-pair', NULL, NULL, 1, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000003.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-14 21:33:16', '2024-10-14 21:33:16'),
(124, 1, 'review', 'right', 6, 20, NULL, 'match-the-pair', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000003.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-14 21:33:19', '2024-10-14 21:33:19'),
(125, 1, 'review', 'wrong', 6, 20, NULL, 'match-the-pair', NULL, NULL, 3, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-14 21:34:53', '2024-10-14 21:34:53'),
(126, 1, 'review', 'right', 6, 20, NULL, 'match-the-pair', NULL, NULL, 2, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000004.00, 'Y', '2024-10-14 21:34:58', '2024-10-14 21:34:58'),
(127, 1, 'exercise', 'right', 6, 20, 42, 'match-the-pair', NULL, NULL, 2, 9, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 01:36:03', '2024-10-15 01:36:03'),
(128, 1, 'exercise', 'right', 6, 20, 42, 'match-the-pair', NULL, NULL, 1, 9, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 01:36:14', '2024-10-15 01:36:14'),
(129, 1, 'exercise', 'right', 6, 20, 42, 'match-the-pair', NULL, NULL, 3, 9, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 01:36:21', '2024-10-15 01:36:21'),
(130, 1, 'exercise', 'right', 6, 20, 42, 'match-the-pair', NULL, NULL, 4, 9, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 01:36:26', '2024-10-15 01:36:26'),
(131, 1, 'review', 'wrong', 6, 20, NULL, 'multiple-choice', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 15:50:49', '2024-10-15 15:50:49'),
(132, 1, 'review', 'wrong', 6, 20, NULL, 'multiple-choice', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 15:52:41', '2024-10-15 15:52:41'),
(133, 1, 'review', 'right', 6, 20, NULL, 'multiple-choice', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000003.00, 'Y', '2024-10-15 15:53:05', '2024-10-15 15:53:05'),
(134, 1, 'review', 'right', 6, 20, NULL, 'multiple-choice', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000004.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 15:53:21', '2024-10-15 15:53:21'),
(135, 1, 'review', 'wrong', 6, 20, NULL, 'multiple-choice', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 15:53:51', '2024-10-15 15:53:51'),
(136, 1, 'review', 'wrong', 6, 20, NULL, 'anagram', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 15:55:30', '2024-10-15 15:55:30'),
(137, 1, 'review', 'wrong', 6, 20, NULL, 'anagram', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 15:55:56', '2024-10-15 15:55:56'),
(138, 1, 'review', 'right', 6, 20, NULL, 'multiple-choice', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000002.00, 00000000.00, 00000000.00, 00000002.00, 'Y', '2024-10-15 15:56:00', '2024-10-15 15:56:00'),
(139, 1, 'review', 'wrong', 6, 20, NULL, 'anagram', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 15:56:19', '2024-10-15 15:56:19'),
(140, 1, 'review', 'right', 6, 20, NULL, 'multiple-choice', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000003.00, 'Y', '2024-10-15 15:56:25', '2024-10-15 15:56:25'),
(141, 1, 'review', 'wrong', 6, 20, NULL, 'multiple-choice', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 15:56:30', '2024-10-15 15:56:30'),
(142, 1, 'review', 'wrong', 6, 20, NULL, 'multiple-choice', NULL, NULL, 3, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 15:56:32', '2024-10-15 15:56:32'),
(143, 1, 'review', 'wrong', 6, 20, NULL, 'anagram', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 15:57:16', '2024-10-15 15:57:16'),
(144, 1, 'review', 'wrong', 6, 20, NULL, 'anagram', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 15:58:02', '2024-10-15 15:58:02'),
(145, 1, 'review', 'right', 6, 20, NULL, 'match-the-pair', NULL, NULL, 1, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000003.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 15:58:33', '2024-10-15 15:58:33'),
(146, 1, 'review', 'right', 6, 20, NULL, 'match-the-pair', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000003.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 15:58:34', '2024-10-15 15:58:34'),
(147, 1, 'review', 'right', 6, 20, NULL, 'match-the-pair', NULL, NULL, 2, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000003.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 15:58:36', '2024-10-15 15:58:36'),
(148, 1, 'review', 'right', 6, 20, NULL, 'match-the-pair', NULL, NULL, 3, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000003.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 15:58:38', '2024-10-15 15:58:38'),
(149, 1, 'review', 'wrong', 6, 20, NULL, 'anagram', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 16:00:46', '2024-10-15 16:00:46'),
(150, 1, 'review', 'wrong', 6, 20, NULL, 'multiple-choice', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 16:03:54', '2024-10-15 16:03:54'),
(151, 1, 'review', 'right', 6, 20, NULL, 'multiple-choice', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000003.00, 'Y', '2024-10-15 16:04:24', '2024-10-15 16:04:24'),
(152, 1, 'review', 'wrong', 6, 20, NULL, 'match-the-pair', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 16:04:40', '2024-10-15 16:04:40'),
(153, 1, 'review', 'right', 6, 20, NULL, 'multiple-choice', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000004.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 16:06:10', '2024-10-15 16:06:10'),
(154, 1, 'review', 'wrong', 6, 20, NULL, 'anagram', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 16:06:49', '2024-10-15 16:06:49'),
(155, 1, 'review', 'wrong', 6, 20, NULL, 'anagram', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 16:07:27', '2024-10-15 16:07:27'),
(156, 1, 'review', 'wrong', 6, 20, NULL, 'anagram', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 16:08:32', '2024-10-15 16:08:32'),
(157, 1, 'review', 'right', 6, 20, NULL, 'multiple-choice', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000003.00, 'Y', '2024-10-15 16:09:36', '2024-10-15 16:09:36'),
(158, 1, 'review', 'wrong', 6, 20, NULL, 'match-the-pair', NULL, NULL, 3, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 16:11:20', '2024-10-15 16:11:20'),
(159, 1, 'review', 'right', 6, 20, NULL, 'multiple-choice', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000004.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 16:12:22', '2024-10-15 16:12:22'),
(160, 1, 'review', 'wrong', 6, 20, NULL, 'match-the-pair', NULL, NULL, 3, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 16:12:40', '2024-10-15 16:12:40'),
(161, 1, 'review', 'wrong', 6, 20, NULL, 'anagram', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 16:58:13', '2024-10-15 16:58:13'),
(162, 1, 'review', 'wrong', 6, 20, NULL, 'multiple-choice', NULL, NULL, 3, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 17:00:46', '2024-10-15 17:00:46'),
(163, 1, 'review', 'right', 6, 20, NULL, 'multiple-choice', NULL, NULL, 3, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000003.00, 'Y', '2024-10-15 17:01:12', '2024-10-15 17:01:12'),
(164, 1, 'review', 'wrong', 6, 20, NULL, 'anagram', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 17:02:07', '2024-10-15 17:02:07'),
(165, 1, 'review', 'wrong', 6, 20, NULL, 'multiple-choice', NULL, NULL, 3, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 17:03:42', '2024-10-15 17:03:42'),
(166, 1, 'review', 'wrong', 6, 20, NULL, 'anagram', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 17:04:15', '2024-10-15 17:04:15'),
(167, 1, 'review', 'right', 6, 20, NULL, 'match-the-pair', NULL, NULL, 1, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000004.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 17:04:47', '2024-10-15 17:04:47'),
(168, 1, 'review', 'wrong', 6, 20, NULL, 'match-the-pair', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 17:05:00', '2024-10-15 17:05:00'),
(169, 1, 'review', 'right', 6, 20, NULL, 'multiple-choice', NULL, NULL, 3, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000004.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 17:06:23', '2024-10-15 17:06:23'),
(170, 1, 'review', 'right', 6, 20, NULL, 'match-the-pair', NULL, NULL, 1, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000004.00, 'Y', '2024-10-15 17:06:35', '2024-10-15 17:06:35'),
(171, 1, 'review', 'right', 6, 20, NULL, 'match-the-pair', NULL, NULL, 1, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000004.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 17:06:46', '2024-10-15 17:06:46'),
(172, 1, 'review', 'wrong', 6, 20, NULL, 'anagram', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 17:07:17', '2024-10-15 17:07:17'),
(173, 1, 'review', 'wrong', 6, 20, NULL, 'anagram', NULL, NULL, 3, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 17:09:33', '2024-10-15 17:09:33'),
(174, 1, 'review', 'wrong', 6, 20, NULL, 'anagram', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 17:10:08', '2024-10-15 17:10:08'),
(175, 1, 'review', 'wrong', 6, 20, NULL, 'anagram', NULL, NULL, 3, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 17:10:27', '2024-10-15 17:10:27'),
(176, 1, 'review', 'wrong', 6, 20, NULL, 'multiple-choice', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 17:11:41', '2024-10-15 17:11:41'),
(177, 1, 'review', 'wrong', 6, 20, NULL, 'multiple-choice', NULL, NULL, 3, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 17:11:47', '2024-10-15 17:11:47'),
(178, 1, 'review', 'right', 6, 20, NULL, 'match-the-pair', NULL, NULL, 1, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000004.00, 'Y', '2024-10-15 17:12:49', '2024-10-15 17:12:49'),
(179, 1, 'review', 'wrong', 6, 20, NULL, 'multiple-choice', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 17:13:54', '2024-10-15 17:13:54'),
(180, 1, 'review', 'wrong', 6, 20, NULL, 'multiple-choice', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 17:13:57', '2024-10-15 17:13:57'),
(181, 1, 'review', 'right', 6, 20, NULL, 'match-the-pair', NULL, NULL, 1, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000004.00, 'Y', '2024-10-15 17:13:59', '2024-10-15 17:13:59'),
(182, 1, 'review', 'wrong', 6, 20, NULL, 'match-the-pair', NULL, NULL, 3, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 17:14:03', '2024-10-15 17:14:03'),
(183, 1, 'review', 'wrong', 6, 20, NULL, 'match-the-pair', NULL, NULL, 3, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 17:17:52', '2024-10-15 17:17:52'),
(184, 1, 'review', 'wrong', 6, 20, NULL, 'multiple-choice', NULL, NULL, 3, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 17:20:07', '2024-10-15 17:20:07'),
(185, 1, 'review', 'wrong', 6, 20, NULL, 'multiple-choice', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 17:43:52', '2024-10-15 17:43:52'),
(186, 1, 'review', 'wrong', 6, 20, NULL, 'multiple-choice', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 17:43:55', '2024-10-15 17:43:55'),
(187, 1, 'review', 'wrong', 6, 20, NULL, 'multiple-choice', NULL, NULL, 3, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 17:46:39', '2024-10-15 17:46:39'),
(188, 1, 'review', 'wrong', 6, 20, NULL, 'anagram', NULL, NULL, 3, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 17:46:43', '2024-10-15 17:46:43'),
(189, 1, 'review', 'right', 6, 20, NULL, 'match-the-pair', NULL, NULL, 1, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000004.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 17:46:47', '2024-10-15 17:46:47'),
(190, 1, 'review', 'wrong', 6, 20, NULL, 'match-the-pair', NULL, NULL, 3, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 17:46:50', '2024-10-15 17:46:50'),
(191, 1, 'review', 'right', 6, 20, NULL, 'match-the-pair', NULL, NULL, 2, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000004.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 17:46:51', '2024-10-15 17:46:51'),
(192, 1, 'review', 'right', 6, 20, NULL, 'match-the-pair', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000004.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 17:46:53', '2024-10-15 17:46:53'),
(193, 1, 'exercise', 'wrong', 6, 21, 47, 'match-the-pair', NULL, NULL, 3, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 18:55:19', '2024-10-15 18:55:19'),
(194, 1, 'exercise', 'wrong', 6, 21, 48, 'match-the-pair', NULL, NULL, 3, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-15 18:56:20', '2024-10-15 18:56:20'),
(195, 1, 'review', 'wrong', 6, 20, NULL, 'multiple-choice', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-16 23:39:18', '2024-10-16 23:39:18'),
(196, 1, 'review', 'wrong', 6, 20, NULL, 'multiple-choice', NULL, NULL, 2, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-16 23:39:20', '2024-10-16 23:39:20'),
(197, 1, 'review', 'wrong', 6, 20, NULL, 'anagram', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-16 23:47:19', '2024-10-16 23:47:19'),
(198, 1, 'review', 'wrong', 6, 20, NULL, 'multiple-choice', NULL, NULL, 2, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-16 23:49:35', '2024-10-16 23:49:35'),
(199, 1, 'review', 'wrong', 6, 20, NULL, 'match-the-pair', NULL, NULL, 1, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-16 23:52:30', '2024-10-16 23:52:30'),
(200, 1, 'review', 'right', 6, 20, NULL, 'multiple-choice', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000003.00, 'Y', '2024-10-16 23:54:27', '2024-10-16 23:54:27'),
(201, 1, 'review', 'wrong', 6, 20, NULL, 'multiple-choice', NULL, NULL, 2, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-16 23:54:57', '2024-10-16 23:54:57'),
(202, 1, 'review', 'wrong', 6, 20, NULL, 'anagram', NULL, NULL, 3, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-16 23:56:07', '2024-10-16 23:56:07'),
(203, 1, 'exercise', 'right', 3, 16, 2, 'multiple-choice', NULL, NULL, 1, 4, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-17 19:36:34', '2024-10-17 19:36:34'),
(204, 1, 'exercise', 'wrong', 3, 17, 8, 'multiple-choice', NULL, NULL, 3, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-17 19:58:59', '2024-10-17 19:58:59'),
(205, 1, 'exercise', 'wrong', 3, 17, 8, 'multiple-choice', NULL, NULL, 3, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-17 23:46:22', '2024-10-17 23:46:22'),
(206, 1, 'exercise', 'right', 3, 17, 8, 'multiple-choice', NULL, NULL, 3, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000001.00, 00000000.00, 00000000.00, 00000001.00, 'Y', '2024-10-17 23:58:51', '2024-10-17 23:58:51'),
(207, 1, 'exercise', 'wrong', 3, 17, 14, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-17 23:58:54', '2024-10-17 23:58:54'),
(208, 1, 'exercise', 'right', 3, 17, 8, 'multiple-choice', NULL, NULL, 3, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000001.00, 00000000.00, 00000000.00, 00000001.00, 'Y', '2024-10-17 23:59:29', '2024-10-17 23:59:29'),
(209, 1, 'exercise', 'wrong', 3, 17, 14, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-17 23:59:33', '2024-10-17 23:59:33'),
(210, 1, 'exercise', 'right', 3, 17, 25, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000002.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-18 00:14:54', '2024-10-18 00:14:54'),
(211, 1, 'exercise', 'wrong', 3, 17, 26, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-18 00:15:55', '2024-10-18 00:15:55'),
(212, 1, 'exercise', 'wrong', 3, 17, 9, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-18 00:25:44', '2024-10-18 00:25:44'),
(213, 1, 'exercise', 'wrong', 3, 17, 10, 'multiple-choice', NULL, NULL, 3, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-18 00:26:28', '2024-10-18 00:26:28'),
(214, 1, 'exercise', 'wrong', 3, 17, 8, 'multiple-choice', NULL, NULL, 3, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-18 00:39:41', '2024-10-18 00:39:41'),
(215, 1, 'exercise', 'wrong', 3, 17, 8, 'multiple-choice', NULL, NULL, 3, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-18 00:41:39', '2024-10-18 00:41:39'),
(216, 1, 'exercise', 'right', 3, 17, 20, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000002.00, 'Y', '2024-10-18 16:54:53', '2024-10-18 16:54:53'),
(217, 1, 'exercise', 'right', 3, 17, 7, 'multiple-choice', NULL, NULL, 2, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000001.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-18 17:08:55', '2024-10-18 17:08:55'),
(218, 1, 'exercise', 'right', 3, 17, 7, 'multiple-choice', NULL, NULL, 2, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000001.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-18 17:09:11', '2024-10-18 17:09:11'),
(219, 1, 'exercise', 'wrong', 3, 17, 13, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-18 17:09:14', '2024-10-18 17:09:14'),
(220, 1, 'exercise', 'wrong', 3, 17, 14, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-18 17:09:23', '2024-10-18 17:09:23');
INSERT INTO `user_activities` (`id`, `user_id`, `activity_type`, `type`, `level_id`, `unit_id`, `exercise_id`, `exercise_type`, `lesson_id`, `lessonframe_id`, `card_id`, `user_unit_activity_id`, `test_id`, `badge_id`, `forumpost_id`, `path_score`, `review_score`, `social_score`, `reading_score`, `writing_score`, `speaking_score`, `listening_score`, `is_temporary`, `created`, `modified`) VALUES
(221, 1, 'exercise', 'right', 3, 17, 17, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000002.00, 00000000.00, 00000000.00, 00000002.00, 'Y', '2024-10-18 17:09:28', '2024-10-18 17:09:28'),
(222, 1, 'exercise', 'wrong', 3, 17, 9, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-18 17:09:32', '2024-10-18 17:09:32'),
(223, 1, 'exercise', 'right', 3, 17, 10, 'multiple-choice', NULL, NULL, 3, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000001.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-18 17:09:50', '2024-10-18 17:09:50'),
(224, 1, 'exercise', 'wrong', 3, 17, 8, 'multiple-choice', NULL, NULL, 3, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-21 15:58:29', '2024-10-21 15:58:29'),
(225, 1, 'exercise', 'right', 3, 17, 22, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000002.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-21 15:59:52', '2024-10-21 15:59:52'),
(226, 1, 'exercise', 'wrong', 3, 17, 23, 'multiple-choice', NULL, NULL, 3, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-21 15:59:55', '2024-10-21 15:59:55'),
(227, 1, 'exercise', 'wrong', 3, 19, 36, 'multiple-choice', NULL, NULL, 2, 7, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-21 20:12:11', '2024-10-21 20:12:11'),
(228, 1, 'exercise', 'right', 3, 17, 8, 'multiple-choice', NULL, NULL, 3, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000001.00, 00000000.00, 00000000.00, 00000001.00, 'Y', '2024-10-21 20:22:58', '2024-10-21 20:22:58'),
(229, 1, 'exercise', 'wrong', 3, 17, 15, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-21 20:41:34', '2024-10-21 20:41:34'),
(230, 1, 'exercise', 'wrong', 3, 17, 21, 'multiple-choice', NULL, NULL, 3, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-21 20:46:16', '2024-10-21 20:46:16'),
(231, 1, 'exercise', 'right', 3, 16, 3, 'multiple-choice', NULL, NULL, 1, 4, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-21 20:47:01', '2024-10-21 20:47:01'),
(232, 1, 'exercise', 'right', 3, 16, 4, 'multiple-choice', NULL, NULL, 1, 4, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-21 20:48:06', '2024-10-21 20:48:06'),
(233, 1, 'exercise', 'wrong', 3, 16, 4, 'multiple-choice', NULL, NULL, 1, 4, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-21 20:56:06', '2024-10-21 20:56:06'),
(234, 1, 'exercise', 'wrong', 3, 16, 4, 'multiple-choice', NULL, NULL, 1, 4, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-21 21:01:34', '2024-10-21 21:01:34'),
(235, 1, 'exercise', 'wrong', 3, 16, 5, 'multiple-choice', NULL, NULL, 1, 4, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-21 21:02:13', '2024-10-21 21:02:13'),
(236, 1, 'exercise', 'right', 3, 17, 7, 'multiple-choice', NULL, NULL, 2, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000001.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-21 21:02:32', '2024-10-21 21:02:32'),
(237, 1, 'exercise', 'wrong', 3, 17, 13, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-21 21:02:34', '2024-10-21 21:02:34'),
(238, 1, 'exercise', 'wrong', 3, 17, 18, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-21 21:02:36', '2024-10-21 21:02:36'),
(239, 1, 'exercise', 'right', 3, 17, 11, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000001.00, 'Y', '2024-10-21 21:05:50', '2024-10-21 21:05:50'),
(240, 1, 'exercise', 'wrong', 3, 17, 25, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-21 21:11:50', '2024-10-21 21:11:50'),
(241, 1, 'exercise', 'wrong', 3, 17, 26, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-22 18:50:29', '2024-10-22 18:50:29'),
(242, 1, 'exercise', 'wrong', 3, 16, 3, 'multiple-choice', NULL, NULL, 1, 4, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-22 19:31:45', '2024-10-22 19:31:45'),
(243, 1, 'exercise', 'right', 3, 16, 3, 'multiple-choice', NULL, NULL, 1, 4, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-22 19:36:07', '2024-10-22 19:36:07'),
(244, 1, 'exercise', 'right', 3, 16, 3, 'multiple-choice', NULL, NULL, 1, 4, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-22 20:02:25', '2024-10-22 20:02:25'),
(245, 1, 'exercise', 'wrong', 3, 16, 3, 'multiple-choice', NULL, NULL, 1, 4, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-22 20:03:38', '2024-10-22 20:03:38'),
(246, 1, 'exercise', 'wrong', 3, 16, 3, 'multiple-choice', NULL, NULL, 1, 4, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-22 20:18:17', '2024-10-22 20:18:17'),
(247, 1, 'exercise', 'right', 3, 16, 4, 'multiple-choice', NULL, NULL, 1, 4, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 16:21:31', '2024-10-23 16:21:31'),
(248, 1, 'exercise', 'wrong', 3, 16, 1, 'multiple-choice', NULL, NULL, 1, 4, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 16:47:50', '2024-10-23 16:47:50'),
(249, 1, 'exercise', 'wrong', 3, 16, 2, 'multiple-choice', NULL, NULL, 1, 4, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 16:47:53', '2024-10-23 16:47:53'),
(250, 1, 'exercise', 'wrong', 3, 16, 3, 'multiple-choice', NULL, NULL, 1, 4, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 17:06:00', '2024-10-23 17:06:00'),
(251, 1, 'exercise', 'right', 3, 16, 3, 'multiple-choice', NULL, NULL, 1, 4, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 17:06:48', '2024-10-23 17:06:48'),
(252, 1, 'exercise', 'right', 3, 16, 4, 'multiple-choice', NULL, NULL, 1, 4, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 17:06:51', '2024-10-23 17:06:51'),
(253, 1, 'exercise', 'right', 3, 16, 3, 'multiple-choice', NULL, NULL, 1, 4, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 17:06:59', '2024-10-23 17:06:59'),
(254, 1, 'exercise', 'right', 3, 16, 3, 'multiple-choice', NULL, NULL, 1, 4, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 17:07:01', '2024-10-23 17:07:01'),
(255, 1, 'exercise', 'wrong', 3, 16, 3, 'multiple-choice', NULL, NULL, 1, 4, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 17:07:04', '2024-10-23 17:07:04'),
(256, 1, 'exercise', 'wrong', 3, 16, 4, 'multiple-choice', NULL, NULL, 1, 4, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 17:07:11', '2024-10-23 17:07:11'),
(257, 1, 'exercise', 'right', 3, 16, 4, 'multiple-choice', NULL, NULL, 1, 4, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 17:07:32', '2024-10-23 17:07:32'),
(258, 1, 'exercise', 'wrong', 3, 16, 1, 'multiple-choice', NULL, NULL, 1, 4, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 17:07:44', '2024-10-23 17:07:44'),
(259, 1, 'exercise', 'wrong', 3, 16, 2, 'multiple-choice', NULL, NULL, 1, 4, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 17:07:46', '2024-10-23 17:07:46'),
(260, 1, 'exercise', 'wrong', 3, 16, 3, 'multiple-choice', NULL, NULL, 1, 4, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 17:07:48', '2024-10-23 17:07:48'),
(261, 1, 'exercise', 'right', 3, 16, 4, 'multiple-choice', NULL, NULL, 1, 4, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 17:07:50', '2024-10-23 17:07:50'),
(262, 1, 'exercise', 'right', 3, 17, 7, 'multiple-choice', NULL, NULL, 2, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000001.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 17:07:59', '2024-10-23 17:07:59'),
(263, 1, 'exercise', 'right', 3, 17, 8, 'multiple-choice', NULL, NULL, 3, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000001.00, 00000000.00, 00000000.00, 00000001.00, 'Y', '2024-10-23 17:08:07', '2024-10-23 17:08:07'),
(264, 1, 'exercise', 'wrong', 3, 17, 14, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 17:08:09', '2024-10-23 17:08:09'),
(265, 1, 'exercise', 'right', 3, 17, 17, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000001.00, 00000000.00, 00000000.00, 00000001.00, 'Y', '2024-10-23 17:08:11', '2024-10-23 17:08:11'),
(266, 1, 'exercise', 'wrong', 3, 17, 9, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 17:08:27', '2024-10-23 17:08:27'),
(267, 1, 'exercise', 'wrong', 3, 17, 21, 'multiple-choice', NULL, NULL, 3, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 17:50:24', '2024-10-23 17:50:24'),
(268, 1, 'exercise', 'right', 3, 17, 12, 'multiple-choice', NULL, NULL, 2, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 17:52:36', '2024-10-23 17:52:36'),
(269, 1, 'exercise', 'right', 3, 17, 28, 'multiple-choice', NULL, NULL, 3, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 17:53:22', '2024-10-23 17:53:22'),
(270, 1, 'exercise', 'wrong', 3, 17, 16, 'multiple-choice', NULL, NULL, 3, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 17:56:15', '2024-10-23 17:56:15'),
(271, 1, 'exercise', 'right', 3, 17, 9, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 18:49:42', '2024-10-23 18:49:42'),
(272, 1, 'exercise', 'wrong', 3, 17, 15, 'multiple-choice', NULL, NULL, 1, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 18:49:53', '2024-10-23 18:49:53'),
(273, 1, 'exercise', 'wrong', 3, 17, 12, 'multiple-choice', NULL, NULL, 2, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 18:59:39', '2024-10-23 18:59:39'),
(274, 1, 'exercise', 'wrong', 3, 17, 24, 'multiple-choice', NULL, NULL, 2, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 20:36:16', '2024-10-23 20:36:16'),
(275, 1, 'exercise', 'wrong', 3, 17, 29, 'multiple-choice', NULL, NULL, 2, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 20:37:16', '2024-10-23 20:37:16'),
(276, 1, 'exercise', 'wrong', 3, 19, 36, 'multiple-choice', NULL, NULL, 2, 7, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 20:39:43', '2024-10-23 20:39:43'),
(277, 1, 'exercise', 'right', 3, 19, 37, 'multiple-choice', NULL, NULL, 4, 7, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000001.00, 'Y', '2024-10-23 20:39:49', '2024-10-23 20:39:49'),
(278, 1, 'exercise', 'wrong', 6, 21, 45, 'match-the-pair', NULL, NULL, 4, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 21:15:42', '2024-10-23 21:15:42'),
(279, 1, 'exercise', 'wrong', 6, 21, 45, 'match-the-pair', NULL, NULL, 4, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 21:15:42', '2024-10-23 21:15:42'),
(280, 1, 'exercise', 'wrong', 6, 21, 45, 'match-the-pair', NULL, NULL, 4, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 21:15:42', '2024-10-23 21:15:42'),
(281, 1, 'exercise', 'wrong', 6, 21, 45, 'match-the-pair', NULL, NULL, 4, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 21:15:42', '2024-10-23 21:15:42'),
(282, 1, 'exercise', 'wrong', 6, 21, 45, 'match-the-pair', NULL, NULL, 3, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 21:15:51', '2024-10-23 21:15:51'),
(283, 1, 'exercise', 'wrong', 6, 21, 45, 'match-the-pair', NULL, NULL, 3, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 21:15:51', '2024-10-23 21:15:51'),
(284, 1, 'exercise', 'right', 6, 21, 45, 'match-the-pair', NULL, NULL, 1, 8, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 21:15:56', '2024-10-23 21:15:56'),
(285, 1, 'exercise', 'right', 6, 21, 43, 'match-the-pair', NULL, NULL, 1, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 21:18:34', '2024-10-23 21:18:34'),
(286, 1, 'exercise', 'wrong', 6, 21, 43, 'match-the-pair', NULL, NULL, 3, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 21:18:38', '2024-10-23 21:18:38'),
(287, 1, 'exercise', 'wrong', 6, 21, 43, 'match-the-pair', NULL, NULL, 4, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 21:18:42', '2024-10-23 21:18:42'),
(288, 1, 'exercise', 'right', 6, 21, 43, 'match-the-pair', NULL, NULL, 2, 8, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000004.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 21:18:44', '2024-10-23 21:18:44'),
(289, 1, 'exercise', 'wrong', 6, 21, 45, 'match-the-pair', NULL, NULL, 3, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 21:18:53', '2024-10-23 21:18:53'),
(290, 1, 'exercise', 'wrong', 6, 21, 45, 'match-the-pair', NULL, NULL, 3, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 21:18:53', '2024-10-23 21:18:53'),
(291, 1, 'exercise', 'wrong', 6, 21, 45, 'match-the-pair', NULL, NULL, 4, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 21:18:59', '2024-10-23 21:18:59'),
(292, 1, 'exercise', 'wrong', 6, 21, 45, 'match-the-pair', NULL, NULL, 4, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 21:18:59', '2024-10-23 21:18:59'),
(293, 1, 'exercise', 'wrong', 6, 21, 46, 'match-the-pair', NULL, NULL, 4, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 21:19:07', '2024-10-23 21:19:07'),
(294, 1, 'exercise', 'wrong', 6, 21, 46, 'match-the-pair', NULL, NULL, 3, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 21:19:12', '2024-10-23 21:19:12'),
(295, 1, 'exercise', 'wrong', 6, 21, 46, 'match-the-pair', NULL, NULL, 2, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 21:19:15', '2024-10-23 21:19:15'),
(296, 1, 'exercise', 'right', 6, 21, 46, 'match-the-pair', NULL, NULL, 1, 8, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-23 21:19:17', '2024-10-23 21:19:17'),
(297, 1, 'exercise', 'right', 6, 20, 42, 'match-the-pair', NULL, NULL, 1, 9, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-24 03:43:53', '2024-10-24 03:43:53'),
(298, 1, 'exercise', 'wrong', 6, 21, 43, 'match-the-pair', NULL, NULL, 4, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-25 17:04:28', '2024-10-25 17:04:28'),
(299, 1, 'exercise', 'right', 6, 21, 43, 'match-the-pair', NULL, NULL, 1, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-25 17:04:31', '2024-10-25 17:04:31'),
(300, 1, 'exercise', 'wrong', 6, 21, 43, 'match-the-pair', NULL, NULL, 3, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-25 17:04:33', '2024-10-25 17:04:33'),
(301, 1, 'exercise', 'right', 6, 21, 43, 'match-the-pair', NULL, NULL, 2, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-25 17:04:36', '2024-10-25 17:04:36'),
(302, 1, 'exercise', 'wrong', 6, 21, 45, 'match-the-pair', NULL, NULL, 3, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-28 16:29:35', '2024-10-28 16:29:35'),
(303, 1, 'exercise', 'wrong', 6, 21, 45, 'match-the-pair', NULL, NULL, 3, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-28 16:29:35', '2024-10-28 16:29:35'),
(304, 1, 'exercise', 'wrong', 6, 21, 45, 'match-the-pair', NULL, NULL, 2, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-28 16:29:42', '2024-10-28 16:29:42'),
(305, 1, 'exercise', 'wrong', 6, 21, 45, 'match-the-pair', NULL, NULL, 2, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-28 16:29:42', '2024-10-28 16:29:42'),
(306, 1, 'exercise', 'wrong', 6, 21, 46, 'match-the-pair', NULL, NULL, 4, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-28 16:30:05', '2024-10-28 16:30:05'),
(307, 1, 'exercise', 'wrong', 6, 21, 46, 'match-the-pair', NULL, NULL, 4, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-28 16:30:05', '2024-10-28 16:30:05'),
(308, 1, 'exercise', 'right', 6, 21, 46, 'match-the-pair', NULL, NULL, 1, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-28 16:30:08', '2024-10-28 16:30:08'),
(309, 1, 'exercise', 'right', 6, 21, 46, 'match-the-pair', NULL, NULL, 2, 8, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-28 16:30:12', '2024-10-28 16:30:12'),
(310, 1, 'exercise', 'wrong', 3, 17, 7, 'multiple-choice', NULL, NULL, 2, 5, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-28 23:08:40', '2024-10-28 23:08:40'),
(311, 1, 'exercise', 'wrong', 7, 23, 50, 'anagram', NULL, NULL, 1, 10, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-29 17:01:53', '2024-10-29 17:01:53'),
(312, 1, 'exercise', 'wrong', 7, 23, 51, 'anagram', NULL, NULL, 1, 10, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-10-29 17:01:58', '2024-10-29 17:01:58'),
(313, 1, 'exercise', 'wrong', 7, 22, 49, 'anagram', NULL, NULL, 1, 11, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-02 15:26:53', '2024-11-02 15:26:53'),
(314, 1, 'exercise', 'wrong', 7, 22, 53, 'anagram', NULL, NULL, 1, 11, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-02 15:26:58', '2024-11-02 15:26:58'),
(315, 1, 'exercise', 'wrong', 7, 22, 54, 'anagram', NULL, NULL, 2, 11, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-02 15:27:03', '2024-11-02 15:27:03'),
(316, 1, 'exercise', 'wrong', 7, 22, 55, 'anagram', NULL, NULL, 2, 11, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-02 15:27:07', '2024-11-02 15:27:07'),
(317, 1, 'exercise', 'wrong', 7, 22, 56, 'anagram', NULL, NULL, 3, 11, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-02 15:27:09', '2024-11-02 15:27:09'),
(318, 1, 'exercise', 'wrong', 7, 22, 57, 'anagram', NULL, NULL, 4, 11, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-02 15:27:13', '2024-11-02 15:27:13'),
(319, 1, 'exercise', 'wrong', 7, 22, 58, 'anagram', NULL, NULL, 1, 11, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-02 15:27:17', '2024-11-02 15:27:17'),
(320, 1, 'exercise', 'wrong', 7, 22, 59, 'anagram', NULL, NULL, 1, 11, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-02 15:27:47', '2024-11-02 15:27:47'),
(321, 1, 'exercise', 'wrong', 7, 22, 60, 'anagram', NULL, NULL, 2, 11, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-02 15:28:02', '2024-11-02 15:28:02'),
(322, 1, 'exercise', 'wrong', 7, 22, 61, 'anagram', NULL, NULL, 3, 11, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-02 15:28:08', '2024-11-02 15:28:08'),
(323, 1, 'exercise', 'wrong', 7, 22, 49, 'anagram', NULL, NULL, 1, 11, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-02 15:36:58', '2024-11-02 15:36:58'),
(324, 1, 'exercise', 'wrong', 7, 22, 53, 'anagram', NULL, NULL, 1, 11, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-02 15:37:00', '2024-11-02 15:37:00'),
(325, 1, 'exercise', 'right', 8, 24, 62, 'truefalse', NULL, NULL, 1, 12, NULL, NULL, NULL, 00000002.00, 00000000.00, 00000000.00, 00000003.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-02 16:34:14', '2024-11-02 16:34:14'),
(326, 1, 'exercise', 'right', 8, 24, 63, 'truefalse', NULL, NULL, 2, 12, NULL, NULL, NULL, 00000002.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000003.00, 'Y', '2024-11-02 16:34:21', '2024-11-02 16:34:21'),
(327, 1, 'exercise', 'right', 8, 24, 64, 'truefalse', NULL, NULL, 3, 12, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-02 16:59:40', '2024-11-02 16:59:40'),
(328, 1, 'exercise', 'wrong', 8, 24, 65, 'truefalse', NULL, NULL, 4, 12, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-02 17:04:51', '2024-11-02 17:04:51'),
(329, 1, 'exercise', 'wrong', 8, 24, 66, 'truefalse', NULL, NULL, 3, 12, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-02 17:04:56', '2024-11-02 17:04:56'),
(330, 1, 'exercise', 'right', 8, 24, 65, 'truefalse', NULL, NULL, 4, 12, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-02 17:04:59', '2024-11-02 17:04:59'),
(331, 1, 'exercise', 'wrong', 8, 24, 62, 'truefalse', NULL, NULL, 1, 12, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-02 17:08:55', '2024-11-02 17:08:55'),
(332, 1, 'exercise', 'right', 8, 25, 67, 'truefalse', NULL, NULL, 2, 13, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000001.00, 00000000.00, 00000000.00, 00000002.00, 'Y', '2024-11-02 17:54:01', '2024-11-02 17:54:01'),
(333, 1, 'exercise', 'right', 8, 25, 68, 'truefalse', NULL, NULL, 1, 13, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-02 17:54:08', '2024-11-02 17:54:08'),
(334, 1, 'exercise', 'wrong', 8, 25, 69, 'truefalse', NULL, NULL, 2, 13, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-02 17:54:10', '2024-11-02 17:54:10'),
(335, 1, 'exercise', 'wrong', 8, 25, 70, 'truefalse', NULL, NULL, 4, 13, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-02 17:54:12', '2024-11-02 17:54:12'),
(336, 1, 'exercise', 'right', 8, 25, 71, 'truefalse', NULL, NULL, 2, 13, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000004.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-02 17:54:14', '2024-11-02 17:54:14'),
(337, 1, 'exercise', 'wrong', 8, 25, 69, 'truefalse', NULL, NULL, 2, 13, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-02 17:54:21', '2024-11-02 17:54:21'),
(338, 1, 'exercise', 'right', 8, 25, 70, 'truefalse', NULL, NULL, 4, 13, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000002.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-02 17:54:24', '2024-11-02 17:54:24'),
(339, 1, 'exercise', 'right', 8, 25, 69, 'truefalse', NULL, NULL, 2, 13, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000001.00, 'Y', '2024-11-02 17:54:28', '2024-11-02 17:54:28'),
(340, 1, 'exercise', 'wrong', 8, 26, 72, 'truefalse', NULL, NULL, 1, 14, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-02 17:54:40', '2024-11-02 17:54:40'),
(341, 1, 'exercise', 'right', 8, 26, 73, 'truefalse', NULL, NULL, 1, 14, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000003.00, 'Y', '2024-11-02 17:54:42', '2024-11-02 17:54:42'),
(342, 1, 'exercise', 'right', 8, 27, 77, 'truefalse', NULL, NULL, NULL, 15, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000006.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-03 22:17:52', '2024-11-03 22:17:52'),
(343, 1, 'exercise', 'wrong', 8, 27, 78, 'truefalse', NULL, NULL, NULL, 15, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-03 22:26:03', '2024-11-03 22:26:03'),
(344, 1, 'exercise', 'right', 8, 27, 78, 'truefalse', NULL, NULL, NULL, 15, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000003.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-03 22:26:09', '2024-11-03 22:26:09'),
(345, 1, 'exercise', 'wrong', 8, 26, 74, 'truefalse', NULL, NULL, 1, 14, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-06 02:40:46', '2024-11-06 02:40:46'),
(346, 1, 'exercise', 'right', 8, 26, 75, 'truefalse', NULL, NULL, 2, 14, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-06 02:40:48', '2024-11-06 02:40:48'),
(347, 1, 'exercise', 'right', 8, 26, 76, 'truefalse', NULL, NULL, 2, 14, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000003.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-06 02:40:50', '2024-11-06 02:40:50'),
(348, 1, 'exercise', 'right', 8, 27, 78, 'truefalse', NULL, NULL, NULL, 16, NULL, NULL, NULL, 00000002.00, 00000000.00, 00000000.00, 00000003.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-06 03:40:37', '2024-11-06 03:40:37'),
(349, 1, 'exercise', NULL, 8, 27, NULL, NULL, NULL, NULL, NULL, 16, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-06 03:40:38', '2024-11-06 03:40:38'),
(350, 1, NULL, NULL, 8, 27, NULL, NULL, NULL, NULL, NULL, 16, NULL, NULL, NULL, 00000008.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-06 03:40:39', '2024-11-06 03:40:39'),
(351, 1, 'exercise', 'wrong', 9, 29, 81, 'fill_in_the_blanks', NULL, NULL, 1, 17, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-06 04:01:03', '2024-11-06 04:01:03'),
(352, 1, 'exercise', 'right', 9, 29, 81, 'fill_in_the_blanks', NULL, NULL, 1, 17, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000003.00, 00000000.00, 00000000.00, 'Y', '2024-11-06 04:01:15', '2024-11-06 04:01:15'),
(353, 1, 'exercise', 'wrong', 9, 29, 81, 'fill_in_the_blanks', NULL, NULL, 1, 18, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-07 18:56:54', '2024-11-07 18:56:54'),
(354, 1, 'exercise', 'right', 9, 32, 85, 'fill_in_the_blanks', NULL, NULL, 3, 19, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000002.00, 00000004.00, 00000000.00, 00000000.00, 'Y', '2024-11-07 19:53:16', '2024-11-07 19:53:16'),
(355, 1, 'exercise', 'right', 9, 32, 86, 'fill_in_the_blanks', NULL, NULL, NULL, 19, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-07 19:54:44', '2024-11-07 19:54:44'),
(356, 1, 'exercise', NULL, 9, 32, NULL, NULL, NULL, NULL, NULL, 19, NULL, NULL, NULL, 00000005.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-07 19:54:45', '2024-11-07 19:54:45'),
(357, 1, NULL, NULL, 9, 32, NULL, NULL, NULL, NULL, NULL, 19, NULL, NULL, NULL, 00000015.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-07 19:54:46', '2024-11-07 19:54:46'),
(358, 1, 'exercise', 'wrong', 10, 31, 83, 'fill_in_the_blanks', NULL, NULL, 1, 20, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-07 19:57:01', '2024-11-07 19:57:01'),
(359, 1, 'exercise', 'wrong', 9, 29, 81, 'fill_in_the_blanks', NULL, NULL, 1, 18, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-07 20:11:21', '2024-11-07 20:11:21'),
(360, 1, 'exercise', 'right', 10, 30, 82, 'fill_in_the_blanks', NULL, NULL, 2, 21, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000002.00, 00000004.00, 00000000.00, 00000000.00, 'Y', '2024-11-07 23:52:28', '2024-11-07 23:52:28'),
(361, 1, 'exercise', NULL, 10, 30, NULL, NULL, NULL, NULL, NULL, 21, NULL, NULL, NULL, 00000005.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-07 23:52:30', '2024-11-07 23:52:30'),
(362, 1, NULL, NULL, 10, 30, NULL, NULL, NULL, NULL, NULL, 21, NULL, NULL, NULL, 00000015.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-07 23:52:30', '2024-11-07 23:52:30'),
(363, 1, 'exercise', 'wrong', 10, 30, 82, 'fill_in_the_blanks', NULL, NULL, 2, 22, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-07 23:55:09', '2024-11-07 23:55:09'),
(364, 1, 'exercise', 'wrong', 10, 30, 82, 'fill_in_the_blanks', NULL, NULL, 2, 22, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-07 23:55:17', '2024-11-07 23:55:17'),
(365, 1, 'exercise', 'wrong', 9, 29, 81, 'fill_in_the_blanks', NULL, NULL, 1, 18, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-07 23:55:31', '2024-11-07 23:55:31'),
(366, 1, 'exercise', 'wrong', 9, 29, 81, 'fill_in_the_blanks', NULL, NULL, 1, 18, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-07 23:56:30', '2024-11-07 23:56:30'),
(367, 1, 'exercise', 'wrong', 7, 34, 88, 'anagram', NULL, NULL, 3, 23, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-08 19:25:57', '2024-11-08 19:25:57'),
(368, 1, 'exercise', 'wrong', 7, 34, 88, 'anagram', NULL, NULL, 2, 23, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-08 19:26:00', '2024-11-08 19:26:00'),
(369, 1, 'exercise', 'right', 6, 36, 91, 'match-the-pair', NULL, NULL, NULL, 24, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000002.00, 00000000.00, 00000000.00, 00000002.00, 'Y', '2024-11-09 01:00:08', '2024-11-09 01:00:08'),
(370, 1, 'exercise', 'wrong', 6, 36, 91, 'match-the-pair', NULL, NULL, NULL, 24, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-09 01:00:12', '2024-11-09 01:00:12'),
(371, 1, 'exercise', 'right', 6, 36, 91, 'match-the-pair', NULL, NULL, NULL, 24, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-09 01:00:16', '2024-11-09 01:00:16'),
(372, 1, 'exercise', 'right', 6, 36, 91, 'match-the-pair', NULL, NULL, NULL, 24, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-09 01:00:19', '2024-11-09 01:00:19'),
(373, 1, 'exercise', 'right', 6, 36, 92, 'match-the-pair', NULL, NULL, 1, 24, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000004.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-09 01:01:38', '2024-11-09 01:01:38'),
(374, 1, 'exercise', 'right', 6, 36, 92, 'match-the-pair', NULL, NULL, 2, 24, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000004.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-09 01:01:42', '2024-11-09 01:01:42'),
(375, 1, 'exercise', 'right', 6, 36, 92, 'match-the-pair', NULL, NULL, 3, 24, NULL, NULL, NULL, 00000003.00, 00000000.00, 00000000.00, 00000004.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-11-09 01:01:45', '2024-11-09 01:01:45'),
(376, 1, 'exercise', 'wrong', 3, 35, 89, 'multiple-choice', NULL, NULL, NULL, 25, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-12-11 17:17:09', '2024-12-11 17:17:09'),
(377, 1, 'exercise', 'wrong', 3, 35, 90, 'multiple-choice', NULL, NULL, 1, 25, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-12-11 17:17:12', '2024-12-11 17:17:12'),
(378, 1, 'exercise', 'wrong', 3, 35, 89, 'multiple-choice', NULL, NULL, NULL, 25, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2024-12-11 17:22:26', '2024-12-11 17:22:26'),
(379, 1, 'exercise', 'right', 10, 30, 95, 'fill_in_the_blanks', NULL, NULL, 1, 22, NULL, NULL, NULL, 00000002.00, 00000000.00, 00000000.00, 00000000.00, 00000003.00, 00000000.00, 00000000.00, 'Y', '2025-03-15 19:10:19', '2025-03-15 19:10:19'),
(380, 1, 'exercise', 'wrong', 10, 30, 96, 'fill_in_the_blanks', NULL, NULL, 1, 22, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-19 18:10:53', '2025-03-19 18:10:53'),
(381, 1, 'exercise', 'wrong', 9, 29, 81, 'fill_in_the_blanks', NULL, NULL, 1, 18, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-21 20:11:56', '2025-03-21 20:11:56'),
(382, 1, 'exercise', 'wrong', 9, 29, 81, 'fill_in_the_blanks', NULL, NULL, 1, 18, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-21 20:11:56', '2025-03-21 20:11:56'),
(383, 1, 'exercise', 'wrong', 9, 29, 81, 'fill_in_the_blanks', NULL, NULL, 1, 18, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-21 20:12:07', '2025-03-21 20:12:07'),
(384, 1, 'exercise', 'wrong', 9, 29, 81, 'fill_in_the_blanks', NULL, NULL, 1, 18, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-21 20:12:08', '2025-03-21 20:12:08'),
(385, 1, 'exercise', 'wrong', 9, 29, 81, 'fill_in_the_blanks', NULL, NULL, 1, 18, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-21 21:22:42', '2025-03-21 21:22:42'),
(386, 1, 'exercise', 'wrong', 9, 29, 81, 'fill_in_the_blanks', NULL, NULL, 1, 18, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-21 21:22:42', '2025-03-21 21:22:42'),
(387, 1, 'exercise', 'wrong', 9, 29, 81, 'fill_in_the_blanks', NULL, NULL, 1, 18, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-21 21:24:42', '2025-03-21 21:24:42'),
(388, 1, 'exercise', 'wrong', 9, 29, 81, 'fill_in_the_blanks', NULL, NULL, 1, 18, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-21 21:25:20', '2025-03-21 21:25:20'),
(389, 1, 'exercise', 'wrong', 9, 29, 81, 'fill_in_the_blanks', NULL, NULL, 1, 18, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-21 21:26:01', '2025-03-21 21:26:01'),
(390, 1, 'exercise', 'wrong', 9, 29, 81, 'fill_in_the_blanks', NULL, NULL, 1, 18, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-21 21:26:02', '2025-03-21 21:26:02'),
(391, 1, 'exercise', 'wrong', 9, 29, 81, 'fill_in_the_blanks', NULL, NULL, 1, 18, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-21 21:26:08', '2025-03-21 21:26:08'),
(392, 1, 'exercise', 'wrong', 9, 29, 81, 'fill_in_the_blanks', NULL, NULL, 1, 18, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-21 21:26:09', '2025-03-21 21:26:09'),
(393, 1, 'exercise', 'wrong', 9, 29, 81, 'fill_in_the_blanks', NULL, NULL, 1, 18, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-21 21:26:16', '2025-03-21 21:26:16'),
(394, 1, 'exercise', 'wrong', 9, 29, 81, 'fill_in_the_blanks', NULL, NULL, 1, 18, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-21 21:26:16', '2025-03-21 21:26:16'),
(395, 1, 'exercise', 'wrong', 9, 29, 81, 'fill_in_the_blanks', NULL, NULL, 1, 18, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-21 21:48:10', '2025-03-21 21:48:10'),
(396, 1, 'exercise', 'wrong', 9, 29, 81, 'fill_in_the_blanks', NULL, NULL, 1, 18, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-21 21:48:10', '2025-03-21 21:48:10'),
(397, 1, 'exercise', 'wrong', 9, 29, 81, 'fill_in_the_blanks', NULL, NULL, 1, 18, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-21 21:48:49', '2025-03-21 21:48:49'),
(398, 1, 'exercise', 'wrong', 9, 29, 81, 'fill_in_the_blanks', NULL, NULL, 1, 18, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-21 21:48:56', '2025-03-21 21:48:56'),
(399, 1, 'exercise', 'wrong', 9, 29, 81, 'fill_in_the_blanks', NULL, NULL, 1, 18, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-21 21:48:56', '2025-03-21 21:48:56'),
(400, 1, 'exercise', 'wrong', 9, 29, 81, 'fill_in_the_blanks', NULL, NULL, 1, 18, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-21 21:50:22', '2025-03-21 21:50:22'),
(401, 1, 'exercise', 'wrong', 9, 29, 81, 'fill_in_the_blanks', NULL, NULL, 1, 18, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-21 21:51:55', '2025-03-21 21:51:55'),
(402, 1, 'exercise', 'wrong', 9, 32, 85, 'fill_in_the_blanks', NULL, NULL, 3, 19, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-21 21:55:05', '2025-03-21 21:55:05'),
(403, 1, 'exercise', 'wrong', 9, 32, 85, 'fill_in_the_blanks', NULL, NULL, 3, 19, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-21 21:55:49', '2025-03-21 21:55:49'),
(404, 1, 'review', 'wrong', 9, NULL, NULL, 'anagram', NULL, NULL, 4, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-21 23:54:20', '2025-03-21 23:54:20'),
(405, 1, 'review', 'wrong', 9, NULL, NULL, 'fill_in_the_blanks', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-22 00:23:31', '2025-03-22 00:23:31'),
(406, 1, 'review', 'wrong', 9, NULL, NULL, 'fill_in_the_blanks', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-22 00:25:30', '2025-03-22 00:25:30'),
(407, 1, 'review', 'wrong', 9, NULL, NULL, 'fill_in_the_blanks', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-22 00:26:52', '2025-03-22 00:26:52'),
(408, 1, 'exercise', 'wrong', 9, 29, 81, 'fill_in_the_blanks', NULL, NULL, 1, 18, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-22 00:27:27', '2025-03-22 00:27:27'),
(409, 1, 'exercise', 'wrong', 9, 32, 85, 'fill_in_the_blanks', NULL, NULL, 3, 19, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-22 00:27:46', '2025-03-22 00:27:46'),
(410, 1, 'exercise', 'wrong', 9, 32, 86, 'fill_in_the_blanks', NULL, NULL, NULL, 19, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-22 00:27:52', '2025-03-22 00:27:52'),
(411, 1, 'exercise', 'wrong', 9, 32, 85, 'fill_in_the_blanks', NULL, NULL, 3, 19, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-22 00:31:26', '2025-03-22 00:31:26'),
(412, 1, 'exercise', 'wrong', 9, 32, 85, 'fill_in_the_blanks', NULL, NULL, 3, 19, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-22 00:34:38', '2025-03-22 00:34:38'),
(413, 1, 'exercise', 'wrong', 9, 32, 85, 'fill_in_the_blanks', NULL, NULL, 3, 19, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-22 00:36:00', '2025-03-22 00:36:00'),
(414, 1, 'exercise', 'wrong', 9, 32, 85, 'fill_in_the_blanks', NULL, NULL, 3, 19, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-22 00:39:18', '2025-03-22 00:39:18'),
(415, 1, 'exercise', 'wrong', 9, 32, 85, 'fill_in_the_blanks', NULL, NULL, 3, 19, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-22 00:40:09', '2025-03-22 00:40:09'),
(416, 1, 'exercise', 'wrong', 9, 32, 85, 'fill_in_the_blanks', NULL, NULL, 3, 19, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-22 00:40:47', '2025-03-22 00:40:47'),
(417, 1, 'exercise', 'wrong', 9, 32, 85, 'fill_in_the_blanks', NULL, NULL, 3, 19, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-22 00:41:34', '2025-03-22 00:41:34'),
(418, 1, 'exercise', 'wrong', 9, 32, 85, 'fill_in_the_blanks', NULL, NULL, 3, 19, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-22 00:45:09', '2025-03-22 00:45:09'),
(419, 1, 'exercise', 'wrong', 9, 32, 85, 'fill_in_the_blanks', NULL, NULL, 3, 19, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-22 23:48:40', '2025-03-22 23:48:40'),
(420, 1, 'exercise', 'wrong', 9, 32, 85, 'fill_in_the_blanks', NULL, NULL, 3, 19, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-22 23:52:35', '2025-03-22 23:52:35'),
(421, 1, 'exercise', 'wrong', 9, 32, 85, 'fill_in_the_blanks', NULL, NULL, 3, 19, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-22 23:52:49', '2025-03-22 23:52:49'),
(422, 1, 'exercise', 'wrong', 9, 32, 85, 'fill_in_the_blanks', NULL, NULL, 3, 19, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-22 23:57:28', '2025-03-22 23:57:28'),
(423, 1, 'exercise', 'wrong', 9, 32, 85, 'fill_in_the_blanks', NULL, NULL, 3, 19, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-23 04:00:01', '2025-03-23 04:00:01'),
(424, 1, 'exercise', 'wrong', 9, 32, 85, 'fill_in_the_blanks', NULL, NULL, 3, 19, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-23 04:02:44', '2025-03-23 04:02:44'),
(425, 1, 'exercise', 'wrong', 9, 32, 85, 'fill_in_the_blanks', NULL, NULL, 3, 19, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-23 04:04:50', '2025-03-23 04:04:50'),
(426, 1, 'exercise', 'wrong', 9, 32, 86, 'fill_in_the_blanks', NULL, NULL, NULL, 19, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-23 04:04:54', '2025-03-23 04:04:54'),
(427, 1, 'exercise', 'wrong', 9, 32, 95, 'fill_in_the_blanks', NULL, NULL, 1, 19, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-23 04:04:56', '2025-03-23 04:04:56'),
(428, 1, 'exercise', 'wrong', 9, 32, 85, 'fill_in_the_blanks', NULL, NULL, 3, 19, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-23 04:06:38', '2025-03-23 04:06:38'),
(429, 1, 'exercise', 'wrong', 9, 32, 85, 'fill_in_the_blanks', NULL, NULL, 3, 19, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-23 04:11:49', '2025-03-23 04:11:49');
INSERT INTO `user_activities` (`id`, `user_id`, `activity_type`, `type`, `level_id`, `unit_id`, `exercise_id`, `exercise_type`, `lesson_id`, `lessonframe_id`, `card_id`, `user_unit_activity_id`, `test_id`, `badge_id`, `forumpost_id`, `path_score`, `review_score`, `social_score`, `reading_score`, `writing_score`, `speaking_score`, `listening_score`, `is_temporary`, `created`, `modified`) VALUES
(430, 1, 'exercise', 'wrong', 9, 32, 85, 'fill_in_the_blanks', NULL, NULL, 3, 19, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-23 04:12:33', '2025-03-23 04:12:33'),
(431, 1, 'exercise', 'wrong', 9, 32, 85, 'fill_in_the_blanks', NULL, NULL, 3, 19, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-23 04:13:25', '2025-03-23 04:13:25'),
(432, 1, 'exercise', 'wrong', 9, 32, 85, 'fill_in_the_blanks', NULL, NULL, 3, 19, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-23 04:15:03', '2025-03-23 04:15:03'),
(433, 1, 'exercise', 'right', 9, 32, 85, 'fill_in_the_blanks', NULL, NULL, 3, 19, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000001.00, 00000001.00, 00000000.00, 00000000.00, 'Y', '2025-03-23 04:18:57', '2025-03-23 04:18:57'),
(434, 1, 'exercise', 'right', 9, 32, 86, 'fill_in_the_blanks', NULL, NULL, NULL, 19, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-23 04:19:05', '2025-03-23 04:19:05'),
(435, 1, 'exercise', 'wrong', 9, 32, 95, 'fill_in_the_blanks', NULL, NULL, 1, 19, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-23 04:19:08', '2025-03-23 04:19:08'),
(436, 1, 'exercise', 'wrong', 9, 32, 85, 'fill_in_the_blanks', NULL, NULL, 3, 19, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-23 04:19:58', '2025-03-23 04:19:58'),
(437, 1, 'exercise', 'wrong', 9, 32, 86, 'fill_in_the_blanks', NULL, NULL, NULL, 19, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-23 04:20:03', '2025-03-23 04:20:03'),
(438, 1, 'exercise', 'right', 9, 32, 95, 'fill_in_the_blanks', NULL, NULL, 1, 19, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000002.00, 00000000.00, 00000000.00, 'Y', '2025-03-23 04:20:16', '2025-03-23 04:20:16'),
(439, 1, 'exercise', 'right', 9, 29, 81, 'fill_in_the_blanks', NULL, NULL, 1, 18, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000001.00, 00000000.00, 00000000.00, 'Y', '2025-03-23 04:20:44', '2025-03-23 04:20:44'),
(440, 1, 'exercise', 'wrong', 9, 29, 81, 'fill_in_the_blanks', NULL, NULL, 1, 26, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-23 04:20:48', '2025-03-23 04:20:48'),
(441, 1, 'exercise', 'wrong', 10, 30, 96, 'fill_in_the_blanks', NULL, NULL, 1, 22, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-23 04:21:08', '2025-03-23 04:21:08'),
(442, 1, 'exercise', 'wrong', 10, 30, 96, 'fill_in_the_blanks', NULL, NULL, 1, 22, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-23 04:21:33', '2025-03-23 04:21:33'),
(443, 1, 'exercise', 'right', 10, 30, 96, 'fill_in_the_blanks', NULL, NULL, 1, 22, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000001.00, 00000000.00, 00000000.00, 'Y', '2025-03-23 04:21:43', '2025-03-23 04:21:43'),
(444, 1, 'exercise', 'wrong', 10, 30, 82, 'fill_in_the_blanks', NULL, NULL, 2, 22, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-23 04:22:01', '2025-03-23 04:22:01'),
(445, 1, 'exercise', 'right', 10, 30, 82, 'fill_in_the_blanks', NULL, NULL, 2, 22, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000001.00, 00000001.00, 00000000.00, 00000000.00, 'Y', '2025-03-23 04:22:59', '2025-03-23 04:22:59'),
(446, 1, 'exercise', 'wrong', 10, 30, 82, 'fill_in_the_blanks', NULL, NULL, 2, 27, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-23 04:23:35', '2025-03-23 04:23:35'),
(447, 1, 'exercise', 'right', 10, 30, 96, 'fill_in_the_blanks', NULL, NULL, 1, 27, NULL, NULL, NULL, 00000001.00, 00000000.00, 00000000.00, 00000000.00, 00000001.00, 00000000.00, 00000000.00, 'Y', '2025-03-23 04:37:25', '2025-03-23 04:37:25'),
(448, 1, 'exercise', 'right', 10, 30, 96, 'fill_in_the_blanks', NULL, NULL, 1, 27, NULL, NULL, NULL, 00000001.00, 00000000.00, 00000000.00, 00000000.00, 00000001.00, 00000000.00, 00000000.00, 'Y', '2025-03-23 23:30:18', '2025-03-23 23:30:18'),
(449, 1, 'exercise', 'wrong', 10, 30, 96, 'fill_in_the_blanks', NULL, NULL, 1, 27, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-25 16:56:53', '2025-03-25 16:56:53'),
(450, 1, 'exercise', 'right', 10, 30, 96, 'fill_in_the_blanks', NULL, NULL, 1, 27, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000001.00, 00000000.00, 00000000.00, 'Y', '2025-03-25 16:57:18', '2025-03-25 16:57:18'),
(451, 1, 'exercise', 'wrong', 10, 30, 82, 'fill_in_the_blanks', NULL, NULL, 2, 27, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-25 16:57:28', '2025-03-25 16:57:28'),
(452, 1, 'exercise', 'wrong', 9, 29, 81, 'fill_in_the_blanks', NULL, NULL, 1, 26, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-03-25 16:57:45', '2025-03-25 16:57:45'),
(453, 1, 'exercise', 'right', 9, 29, 81, 'fill_in_the_blanks', NULL, NULL, 1, 26, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000001.00, 00000000.00, 00000000.00, 'Y', '2025-03-25 16:57:56', '2025-03-25 16:57:56'),
(454, 1, 'exercise', 'right', 9, 32, 85, 'fill_in_the_blanks', NULL, NULL, 3, 28, NULL, NULL, NULL, 00000002.00, 00000000.00, 00000000.00, 00000001.00, 00000002.00, 00000000.00, 00000000.00, 'Y', '2025-03-25 16:58:10', '2025-03-25 16:58:10'),
(455, 1, 'exercise', 'right', 9, 32, 85, 'fill_in_the_blanks', NULL, NULL, 3, 28, NULL, NULL, NULL, 00000002.00, 00000000.00, 00000000.00, 00000001.00, 00000001.00, 00000000.00, 00000000.00, 'Y', '2025-03-25 17:07:27', '2025-03-25 17:07:27'),
(456, 1, 'exercise', 'wrong', 3, 35, 90, 'multiple-choice', NULL, NULL, NULL, 25, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-05-01 18:38:58', '2025-05-01 18:38:58'),
(457, 1, 'exercise', 'wrong', 3, 35, 90, 'multiple-choice', NULL, NULL, NULL, 25, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-05-03 19:30:29', '2025-05-03 19:30:29'),
(458, 1, 'exercise', 'right', 3, 35, 98, 'multiple-choice', NULL, NULL, NULL, 25, NULL, NULL, NULL, 00000004.00, 00000000.00, 00000000.00, 00000007.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-05-03 19:31:34', '2025-05-03 19:31:34'),
(459, 1, 'exercise', 'wrong', 9, 29, 81, 'fill_in_the_blanks', NULL, NULL, 1, 29, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-05-04 22:50:41', '2025-05-04 22:50:41'),
(460, 1, 'review', 'wrong', 9, NULL, NULL, 'fill_in_the_blanks', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-05-14 19:16:58', '2025-05-14 19:16:58'),
(461, 1, 'lesson', NULL, 2, 1, NULL, NULL, 5, 30, NULL, 1, NULL, NULL, NULL, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-05-14 19:17:48', '2025-05-14 19:17:48'),
(462, 1, 'lesson', NULL, 2, 1, NULL, NULL, 16, 107, NULL, 1, NULL, NULL, NULL, 00000005.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-05-14 19:17:51', '2025-05-14 19:17:51'),
(463, 1, 'lesson', NULL, 2, 1, NULL, NULL, 17, 108, NULL, 1, NULL, NULL, NULL, 00000005.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-05-14 19:17:53', '2025-05-14 19:17:53'),
(464, 1, 'lesson', NULL, 2, 1, NULL, NULL, 18, 109, NULL, 1, NULL, NULL, NULL, 00000005.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-05-14 19:17:56', '2025-05-14 19:17:56'),
(465, 1, NULL, NULL, 2, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, 00000015.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 00000000.00, 'Y', '2025-05-14 19:18:00', '2025-05-14 19:18:00');

--
-- Triggers `user_activities`
--
DELIMITER $$
CREATE TRIGGER `UnitReviewEntry` AFTER INSERT ON `user_activities` FOR EACH ROW BEGIN
	IF(NEW.`unit_id` IS NOT NULL) THEN
        	CALL UnitReview(NEW.`user_id`, NEW.`unit_id`);
   END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `user_images`
--

CREATE TABLE `user_images` (
  `id` int(12) UNSIGNED NOT NULL,
  `user_id` int(12) UNSIGNED NOT NULL,
  `image` varchar(255) NOT NULL,
  `aws_link` varchar(500) DEFAULT NULL,
  `feature` enum('Y','N') NOT NULL DEFAULT 'N',
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `user_level_badges`
--

CREATE TABLE `user_level_badges` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `level_id` int(11) UNSIGNED NOT NULL,
  `path_id` int(11) UNSIGNED NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

--
-- Dumping data for table `user_level_badges`
--

INSERT INTO `user_level_badges` (`id`, `user_id`, `level_id`, `path_id`, `created`, `modified`) VALUES
(1, 1, 3, 1, '2024-09-15 16:26:40', '2024-09-15 16:26:40'),
(2, 1, 4, 1, '2024-09-15 16:26:40', '2024-09-15 16:26:40'),
(3, 1, 5, 1, '2024-09-16 12:25:14', '2024-09-16 12:25:14'),
(4, 1, 9, 1, '2024-11-06 04:01:17', '2024-11-06 04:01:17'),
(5, 1, 10, 1, '2024-11-07 23:52:30', '2024-11-07 23:52:30');

-- --------------------------------------------------------

--
-- Table structure for table `user_points`
--

CREATE TABLE `user_points` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `path_score` float NOT NULL,
  `review_score` float NOT NULL,
  `social_score` float NOT NULL,
  `reading_score` float NOT NULL,
  `writing_score` float NOT NULL,
  `speaking_score` float NOT NULL,
  `listening_score` float NOT NULL,
  `total_score` float NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

--
-- Dumping data for table `user_points`
--

INSERT INTO `user_points` (`id`, `user_id`, `path_score`, `review_score`, `social_score`, `reading_score`, `writing_score`, `speaking_score`, `listening_score`, `total_score`, `created`, `modified`) VALUES
(1, 1, 246, 0, 0, 187, 41, 0, 78, 552, '2024-09-15 16:26:36', '2025-05-14 19:17:57');

-- --------------------------------------------------------

--
-- Table structure for table `user_progress`
--

CREATE TABLE `user_progress` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `learningpath_id` int(11) UNSIGNED NOT NULL,
  `level_id` int(11) UNSIGNED NOT NULL,
  `unit_id` int(11) UNSIGNED NOT NULL,
  `lesson_id` int(11) UNSIGNED DEFAULT NULL,
  `exercise_id` int(11) UNSIGNED DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_settings`
--

CREATE TABLE `user_settings` (
  `id` int(12) UNSIGNED NOT NULL,
  `user_id` int(12) UNSIGNED NOT NULL,
  `display_name` varchar(100) NOT NULL DEFAULT '',
  `profile_picture` varchar(256) NOT NULL DEFAULT '',
  `aws_profile_link` varchar(500) DEFAULT NULL,
  `location` varchar(500) NOT NULL DEFAULT '',
  `profile_desc` text,
  `push_notification` enum('0','1') NOT NULL DEFAULT '0',
  `email_notification` enum('0','1') NOT NULL DEFAULT '0',
  `news_event` enum('0','1') NOT NULL DEFAULT '0',
  `motivation` enum('0','1') NOT NULL DEFAULT '0',
  `motivation_time` time DEFAULT NULL,
  `age_over_adult` enum('0','1') NOT NULL DEFAULT '0',
  `parental_lock` varchar(256) DEFAULT '',
  `parental_lock_on` enum('0','1') NOT NULL DEFAULT '0',
  `badges` varchar(100) DEFAULT NULL,
  `public_profile` enum('0','1') NOT NULL DEFAULT '0',
  `public_leaderboard` enum('0','1') NOT NULL DEFAULT '0',
  `audio_archive` enum('0','1') NOT NULL DEFAULT '0',
  `hearing` enum('0','1') NOT NULL DEFAULT '0',
  `lastupdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user_settings`
--

INSERT INTO `user_settings` (`id`, `user_id`, `display_name`, `profile_picture`, `aws_profile_link`, `location`, `profile_desc`, `push_notification`, `email_notification`, `news_event`, `motivation`, `motivation_time`, `age_over_adult`, `parental_lock`, `parental_lock_on`, `badges`, `public_profile`, `public_leaderboard`, `audio_archive`, `hearing`, `lastupdated`) VALUES
(1, 1, '', '', NULL, '', 'Hi, I am mock-admin. I am interested in learning Lakota.', '0', '0', '0', '0', NULL, '1', '', '0', NULL, '1', '1', '0', '0', '2024-11-09 16:48:19'),
(2, 2, '', '', NULL, '', 'Hi, I am user@gmail.com. I am interested in learning Lakota.', '0', '0', '0', '0', NULL, '1', '', '0', NULL, '1', '1', '0', '0', '2024-10-09 16:56:18'),
(3, 3, '', '', NULL, '', 'Hi, I am teacher. I am interested in learning Lakota.', '0', '0', '0', '0', NULL, '0', '', '0', NULL, '0', '0', '0', '0', '2025-07-08 17:53:33');

-- --------------------------------------------------------

--
-- Table structure for table `user_unit_activities`
--

CREATE TABLE `user_unit_activities` (
  `id` int(10) UNSIGNED NOT NULL,
  `path_id` int(10) UNSIGNED DEFAULT NULL,
  `level_id` int(10) UNSIGNED DEFAULT NULL,
  `unit_id` int(10) UNSIGNED DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `percent` int(11) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `user_unit_activities`
--

INSERT INTO `user_unit_activities` (`id`, `path_id`, `level_id`, `unit_id`, `user_id`, `percent`, `created`, `modified`) VALUES
(1, 1, 2, 1, 1, 100, '2024-09-15 16:24:53', '2024-09-15 16:26:40'),
(2, 1, 4, 9, 1, 20, '2024-09-16 07:52:07', '2024-09-16 16:57:34'),
(3, 1, 4, 7, 1, 100, '2024-09-16 12:24:42', '2024-09-16 12:25:14'),
(4, 1, 3, 16, 1, 100, '2024-09-16 18:10:07', '2024-09-16 18:10:45'),
(5, 1, 3, 17, 1, 100, '2024-09-17 15:49:27', '2024-09-17 15:49:46'),
(6, 1, 3, 19, 1, 100, '2024-09-18 11:18:52', '2024-09-26 22:49:19'),
(7, 1, 3, 19, 1, 100, '2024-09-26 22:49:24', '2024-09-26 22:49:24'),
(8, 1, 6, 21, 1, 66, '2024-09-27 19:12:44', '2024-10-23 21:19:30'),
(9, 1, 6, 20, 1, 100, '2024-10-10 23:37:23', '2024-10-10 23:38:13'),
(10, 1, 7, 23, 1, 0, '2024-10-29 17:01:53', '2024-10-29 17:01:53'),
(11, 1, 7, 22, 1, 0, '2024-11-02 15:26:53', '2024-11-02 15:26:53'),
(12, 1, 8, 24, 1, 80, '2024-11-02 16:34:14', '2024-11-02 17:25:30'),
(13, 1, 8, 25, 1, 100, '2024-11-02 17:54:01', '2024-11-02 17:54:30'),
(14, 1, 8, 26, 1, 60, '2024-11-02 17:54:40', '2024-11-06 02:41:05'),
(15, 1, 8, 27, 1, 100, '2024-11-03 22:17:52', '2024-11-03 22:26:11'),
(16, 1, 8, 27, 1, 100, '2024-11-06 03:40:37', '2024-11-06 03:40:37'),
(17, 1, 9, 29, 1, 100, '2024-11-06 04:01:03', '2024-11-06 04:01:17'),
(18, 1, 9, 29, 1, 100, '2024-11-07 18:56:54', '2024-11-07 18:56:54'),
(19, 1, 9, 32, 1, 100, '2024-11-07 19:53:16', '2024-11-07 19:54:46'),
(20, 1, 10, 31, 1, 0, '2024-11-07 19:57:01', '2024-11-07 19:57:01'),
(21, 1, 10, 30, 1, 100, '2024-11-07 23:52:28', '2024-11-07 23:52:30'),
(22, 1, 10, 30, 1, 100, '2024-11-07 23:55:09', '2024-11-07 23:55:09'),
(23, 1, 7, 34, 1, 0, '2024-11-08 19:25:57', '2024-11-08 19:25:57'),
(24, 1, 6, 36, 1, 100, '2024-11-09 01:00:08', '2024-11-09 01:01:48'),
(25, 1, 3, 35, 1, 25, '2024-12-11 17:17:09', '2025-05-03 19:39:03'),
(26, 1, 9, 29, 1, 100, '2025-03-23 04:20:48', '2025-03-23 04:20:48'),
(27, 1, 10, 30, 1, 100, '2025-03-23 04:23:35', '2025-03-23 04:23:35'),
(28, 1, 9, 32, 1, 100, '2025-03-25 16:58:10', '2025-03-25 16:58:10'),
(29, 1, 9, 29, 1, 100, '2025-05-04 22:50:41', '2025-05-04 22:50:41');

-- --------------------------------------------------------

--
-- Table structure for table `wordlinks`
--

CREATE TABLE `wordlinks` (
  `id` int(11) NOT NULL,
  `wordlink` varchar(30) NOT NULL,
  `classroom_id` int(11) DEFAULT NULL,
  `school_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `wordlinks`
--

INSERT INTO `wordlinks` (`id`, `wordlink`, `classroom_id`, `school_id`) VALUES
(1, 'LAKOTALAKOTALAKOTA', NULL, 1),
(2, 'LAKOTALAKOTALAKOTA', NULL, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_types`
--
ALTER TABLE `activity_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `banned_words`
--
ALTER TABLE `banned_words`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bonus_points`
--
ALTER TABLE `bonus_points`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cards`
--
ALTER TABLE `cards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reference_dictionary_id` (`reference_dictionary_id`),
  ADD KEY `video_id` (`video_id`),
  ADD KEY `inflection_id` (`inflection_id`),
  ADD KEY `image_id` (`image_id`),
  ADD KEY `card_type_id` (`card_type_id`);

--
-- Indexes for table `card_card_groups`
--
ALTER TABLE `card_card_groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `card_id` (`card_id`),
  ADD KEY `card_group_id` (`card_group_id`);

--
-- Indexes for table `card_groups`
--
ALTER TABLE `card_groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `card_group_type_id` (`card_group_type_id`);

--
-- Indexes for table `card_group_types`
--
ALTER TABLE `card_group_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `card_types`
--
ALTER TABLE `card_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `card_units`
--
ALTER TABLE `card_units`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_card_units_cards` (`card_id`),
  ADD KEY `FK_card_units_units` (`unit_id`);

--
-- Indexes for table `classrooms`
--
ALTER TABLE `classrooms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `classroom_level_units`
--
ALTER TABLE `classroom_level_units`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `classroom_users`
--
ALTER TABLE `classroom_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contents`
--
ALTER TABLE `contents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `emailcontents`
--
ALTER TABLE `emailcontents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `exercises`
--
ALTER TABLE `exercises`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `exercise_custom_options`
--
ALTER TABLE `exercise_custom_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_exercise_custom_options_exercises` (`exercise_id`),
  ADD KEY `FK_exercise_custom_options_files` (`prompt_audio_id`),
  ADD KEY `FK_exercise_custom_options_files_2` (`prompt_image_id`),
  ADD KEY `FK_exercise_custom_options_files_3` (`response_audio_id`),
  ADD KEY `FK_exercise_custom_options_files_4` (`response_image_id`),
  ADD KEY `FK_exercise_custom_options_exercise_options` (`exercise_option_id`);

--
-- Indexes for table `exercise_options`
--
ALTER TABLE `exercise_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_exercise_options_exercises` (`exercise_id`),
  ADD KEY `FK_exercise_options_cards` (`card_id`),
  ADD KEY `FK_exercise_options_cards_2` (`responce_card_id`),
  ADD KEY `FK_exercise_options_card_groups` (`group_id`);

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_friends_users` (`upload_user_id`);

--
-- Indexes for table `forums`
--
ALTER TABLE `forums`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_forums_learningpaths` (`path_id`),
  ADD KEY `FK_forums_units` (`unit_id`),
  ADD KEY `FK_forums_levels` (`level_id`);

--
-- Indexes for table `forum_flags`
--
ALTER TABLE `forum_flags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_forum_flags_users` (`user_id`),
  ADD KEY `FK_forum_flags_forum_posts` (`post_id`);

--
-- Indexes for table `forum_flag_reasons`
--
ALTER TABLE `forum_flag_reasons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_forum_posts_forum_posts` (`parent_id`),
  ADD KEY `FK_forum_posts_users` (`user_id`),
  ADD KEY `FK_forum_posts_forums` (`forum_id`),
  ADD KEY `FK_forum_posts_forum_flags` (`flag_id`),
  ADD KEY `FK_forum_posts_recording_audios` (`audio`);

--
-- Indexes for table `forum_post_viewers`
--
ALTER TABLE `forum_post_viewers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_forum_posts_users` (`user_id`),
  ADD KEY `FK_forum_posts_forums` (`post_id`);

--
-- Indexes for table `friends`
--
ALTER TABLE `friends`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_friends_users` (`user_id`),
  ADD KEY `FK_friends_users_2` (`friend_id`);

--
-- Indexes for table `global_fires`
--
ALTER TABLE `global_fires`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inflections`
--
ALTER TABLE `inflections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_inflections_dictionary` (`reference_dictionary_id`);

--
-- Indexes for table `learningpaths`
--
ALTER TABLE `learningpaths`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_learningpaths_files` (`image_id`),
  ADD KEY `FK_learningpaths_users` (`owner_id`);

--
-- Indexes for table `learningspeed`
--
ALTER TABLE `learningspeed`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lesson_frames`
--
ALTER TABLE `lesson_frames`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_lesson_frames_lessons` (`lesson_id`),
  ADD KEY `FK_lesson_frames_files` (`audio_id`);

--
-- Indexes for table `lesson_frame_blocks`
--
ALTER TABLE `lesson_frame_blocks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_lesson_frame_blocks_lesson_frames` (`lesson_frame_id`),
  ADD KEY `FK_lesson_frame_blocks_files` (`audio_id`),
  ADD KEY `FK_lesson_frame_blocks_files_2` (`image_id`),
  ADD KEY `FK_lesson_frame_blocks_files_3` (`video_id`),
  ADD KEY `FK_lesson_frame_blocks_cards` (`card_id`);

--
-- Indexes for table `levels`
--
ALTER TABLE `levels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_levels_files` (`image_id`);

--
-- Indexes for table `level_units`
--
ALTER TABLE `level_units`
  ADD PRIMARY KEY (`id`),
  ADD KEY `unit_id_foreign` (`unit_id`),
  ADD KEY `level_id_foreign` (`level_id`),
  ADD KEY `FK_path_level_unit` (`learningpath_id`);

--
-- Indexes for table `passwordreset`
--
ALTER TABLE `passwordreset`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `path_levels`
--
ALTER TABLE `path_levels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `path_id_foreign` (`learningpath_id`),
  ADD KEY `level_id_foreign` (`level_id`);

--
-- Indexes for table `point_references`
--
ALTER TABLE `point_references`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `progress_timers`
--
ALTER TABLE `progress_timers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_progress_timers_learningpaths` (`path_id`),
  ADD KEY `FK_progress_timers_levels` (`level_id`),
  ADD KEY `FK_progress_timers_users` (`user_id`),
  ADD KEY `FK_progress_timers_units` (`unit_id`);

--
-- Indexes for table `recording_audios`
--
ALTER TABLE `recording_audios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_recording_audios_users` (`user_id`),
  ADD KEY `FK_recording_audios_exercises` (`exercise_id`);

--
-- Indexes for table `reference_dictionary`
--
ALTER TABLE `reference_dictionary`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_reference_dictionary_files` (`audio`);

--
-- Indexes for table `review_counters`
--
ALTER TABLE `review_counters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_review_counters_users` (`user_id`),
  ADD KEY `FK_review_counters_levels` (`level_id`),
  ADD KEY `FK_review_counters_units` (`unit_id`);

--
-- Indexes for table `review_queues`
--
ALTER TABLE `review_queues`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_review_queues_users` (`user_id`),
  ADD KEY `FK_review_queues_cards` (`card_id`);

--
-- Indexes for table `review_vars`
--
ALTER TABLE `review_vars`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role` (`role`);

--
-- Indexes for table `schools`
--
ALTER TABLE `schools`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `school_levels`
--
ALTER TABLE `school_levels`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `school_roles`
--
ALTER TABLE `school_roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `school_users`
--
ALTER TABLE `school_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sitesettings`
--
ALTER TABLE `sitesettings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `unit_details`
--
ALTER TABLE `unit_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `unit_id` (`unit_id`),
  ADD KEY `lesson_id` (`lesson_id`),
  ADD KEY `FK_path_unit_lesson` (`learningpath_id`),
  ADD KEY `FK_unit_exercise_id` (`exercise_id`);

--
-- Indexes for table `unit_fires`
--
ALTER TABLE `unit_fires`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_unit_fires_users` (`user_id`),
  ADD KEY `FK_unit_fires_units` (`unit_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_users_roles` (`role_id`),
  ADD KEY `FK_users_learningspeed` (`learningspeed_id`),
  ADD KEY `FK_users_learningpaths` (`learningpath_id`);

--
-- Indexes for table `user_activities`
--
ALTER TABLE `user_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_user_progress_users` (`user_id`),
  ADD KEY `FK_user_activities_levels` (`level_id`),
  ADD KEY `FK_user_activities_units` (`unit_id`),
  ADD KEY `FK_user_activities_exercises` (`exercise_id`),
  ADD KEY `FK_user_activities_lessons` (`lesson_id`),
  ADD KEY `FK_user_activities_cards` (`card_id`),
  ADD KEY `FK_user_activities_lesson_frames` (`lessonframe_id`),
  ADD KEY `FK_user_activities_user_unit_activities` (`user_unit_activity_id`);

--
-- Indexes for table `user_images`
--
ALTER TABLE `user_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_level_badges`
--
ALTER TABLE `user_level_badges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_user_progress_users` (`user_id`),
  ADD KEY `FK_user_level_badges_levels` (`level_id`),
  ADD KEY `FK_user_level_badges_learningpaths` (`path_id`);

--
-- Indexes for table `user_points`
--
ALTER TABLE `user_points`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_user_progress_users` (`user_id`);

--
-- Indexes for table `user_progress`
--
ALTER TABLE `user_progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_user_progress_users` (`user_id`);

--
-- Indexes for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_unit_activities`
--
ALTER TABLE `user_unit_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_user_unit_activities_units` (`unit_id`),
  ADD KEY `FK_user_unit_activities_learningpaths` (`path_id`),
  ADD KEY `FK_user_unit_activities_users` (`user_id`),
  ADD KEY `FK_user_unit_activities_levels` (`level_id`);

--
-- Indexes for table `wordlinks`
--
ALTER TABLE `wordlinks`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `banned_words`
--
ALTER TABLE `banned_words`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1014;

--
-- AUTO_INCREMENT for table `bonus_points`
--
ALTER TABLE `bonus_points`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Primary Key', AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `cards`
--
ALTER TABLE `cards`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `card_card_groups`
--
ALTER TABLE `card_card_groups`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `card_groups`
--
ALTER TABLE `card_groups`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `card_group_types`
--
ALTER TABLE `card_group_types`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `card_types`
--
ALTER TABLE `card_types`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `card_units`
--
ALTER TABLE `card_units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=352;

--
-- AUTO_INCREMENT for table `classrooms`
--
ALTER TABLE `classrooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `classroom_level_units`
--
ALTER TABLE `classroom_level_units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `classroom_users`
--
ALTER TABLE `classroom_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contents`
--
ALTER TABLE `contents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `emailcontents`
--
ALTER TABLE `emailcontents`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `exercises`
--
ALTER TABLE `exercises`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT for table `exercise_custom_options`
--
ALTER TABLE `exercise_custom_options`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `exercise_options`
--
ALTER TABLE `exercise_options`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=774;

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `forums`
--
ALTER TABLE `forums`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `forum_flags`
--
ALTER TABLE `forum_flags`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `forum_flag_reasons`
--
ALTER TABLE `forum_flag_reasons`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `forum_posts`
--
ALTER TABLE `forum_posts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `forum_post_viewers`
--
ALTER TABLE `forum_post_viewers`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `friends`
--
ALTER TABLE `friends`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `global_fires`
--
ALTER TABLE `global_fires`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inflections`
--
ALTER TABLE `inflections`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `learningpaths`
--
ALTER TABLE `learningpaths`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `learningspeed`
--
ALTER TABLE `learningspeed`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- AUTO_INCREMENT for table `lesson_frames`
--
ALTER TABLE `lesson_frames`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=188;

--
-- AUTO_INCREMENT for table `lesson_frame_blocks`
--
ALTER TABLE `lesson_frame_blocks`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=490;

--
-- AUTO_INCREMENT for table `levels`
--
ALTER TABLE `levels`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `level_units`
--
ALTER TABLE `level_units`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `passwordreset`
--
ALTER TABLE `passwordreset`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `path_levels`
--
ALTER TABLE `path_levels`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `point_references`
--
ALTER TABLE `point_references`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT for table `progress_timers`
--
ALTER TABLE `progress_timers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=147;

--
-- AUTO_INCREMENT for table `recording_audios`
--
ALTER TABLE `recording_audios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reference_dictionary`
--
ALTER TABLE `reference_dictionary`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `review_counters`
--
ALTER TABLE `review_counters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `review_queues`
--
ALTER TABLE `review_queues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `review_vars`
--
ALTER TABLE `review_vars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `schools`
--
ALTER TABLE `schools`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `school_levels`
--
ALTER TABLE `school_levels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `school_roles`
--
ALTER TABLE `school_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `school_users`
--
ALTER TABLE `school_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sitesettings`
--
ALTER TABLE `sitesettings`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `unit_details`
--
ALTER TABLE `unit_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=252;

--
-- AUTO_INCREMENT for table `unit_fires`
--
ALTER TABLE `unit_fires`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_activities`
--
ALTER TABLE `user_activities`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=466;

--
-- AUTO_INCREMENT for table `user_images`
--
ALTER TABLE `user_images`
  MODIFY `id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_level_badges`
--
ALTER TABLE `user_level_badges`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_points`
--
ALTER TABLE `user_points`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_progress`
--
ALTER TABLE `user_progress`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_settings`
--
ALTER TABLE `user_settings`
  MODIFY `id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_unit_activities`
--
ALTER TABLE `user_unit_activities`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `wordlinks`
--
ALTER TABLE `wordlinks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cards`
--
ALTER TABLE `cards`
  ADD CONSTRAINT `FK_cards_card_types` FOREIGN KEY (`card_type_id`) REFERENCES `card_types` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK_cards_dictionary` FOREIGN KEY (`reference_dictionary_id`) REFERENCES `reference_dictionary` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK_cards_files` FOREIGN KEY (`image_id`) REFERENCES `files` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK_cards_inflections` FOREIGN KEY (`inflection_id`) REFERENCES `inflections` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK_cards_video` FOREIGN KEY (`video_id`) REFERENCES `files` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION;

--
-- Constraints for table `card_card_groups`
--
ALTER TABLE `card_card_groups`
  ADD CONSTRAINT `FK_card_card_groups_card_groups` FOREIGN KEY (`card_group_id`) REFERENCES `card_groups` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK_card_card_groups_cards` FOREIGN KEY (`card_id`) REFERENCES `cards` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `card_groups`
--
ALTER TABLE `card_groups`
  ADD CONSTRAINT `FK_card_group_type` FOREIGN KEY (`card_group_type_id`) REFERENCES `card_group_types` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `card_units`
--
ALTER TABLE `card_units`
  ADD CONSTRAINT `FK_card_units_cards` FOREIGN KEY (`card_id`) REFERENCES `cards` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK_card_units_units` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `exercise_custom_options`
--
ALTER TABLE `exercise_custom_options`
  ADD CONSTRAINT `FK_exercise_custom_options_exercise_options` FOREIGN KEY (`exercise_option_id`) REFERENCES `exercise_options` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK_exercise_custom_options_exercises` FOREIGN KEY (`exercise_id`) REFERENCES `exercises` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK_exercise_custom_options_files` FOREIGN KEY (`prompt_audio_id`) REFERENCES `files` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK_exercise_custom_options_files_2` FOREIGN KEY (`prompt_image_id`) REFERENCES `files` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK_exercise_custom_options_files_3` FOREIGN KEY (`response_audio_id`) REFERENCES `files` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK_exercise_custom_options_files_4` FOREIGN KEY (`response_image_id`) REFERENCES `files` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION;

--
-- Constraints for table `exercise_options`
--
ALTER TABLE `exercise_options`
  ADD CONSTRAINT `FK_exercise_options_card_groups` FOREIGN KEY (`group_id`) REFERENCES `card_groups` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK_exercise_options_cards` FOREIGN KEY (`card_id`) REFERENCES `cards` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK_exercise_options_cards_2` FOREIGN KEY (`responce_card_id`) REFERENCES `cards` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK_exercise_options_exercises` FOREIGN KEY (`exercise_id`) REFERENCES `exercises` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `forums`
--
ALTER TABLE `forums`
  ADD CONSTRAINT `FK_forums_learningpaths` FOREIGN KEY (`path_id`) REFERENCES `learningpaths` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK_forums_levels` FOREIGN KEY (`level_id`) REFERENCES `levels` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK_forums_units` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
