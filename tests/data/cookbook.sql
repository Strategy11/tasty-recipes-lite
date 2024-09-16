DROP TABLE IF EXISTS `wptests_postmeta`;
CREATE TABLE `wptests_postmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`meta_id`),
  KEY `post_id` (`post_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB AUTO_INCREMENT=2780 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
LOCK TABLES `wptests_postmeta` WRITE;
/*!40000 ALTER TABLE `wptests_postmeta` DISABLE KEYS */;

INSERT INTO `wptests_postmeta` (`meta_id`, `post_id`, `meta_key`, `meta_value`)
VALUES
	(3248, 33771, '_thumbnail_id', '33770'),
	(3249, 33771, 'cookbook_author', 'Lindsay Ostrom'),
	(3250, 33771, 'cookbook_servings', '8'),
	(3251, 33771, 'cookbook_servings_unit', 'tacos'),
	(3252, 33771, 'cookbook_prep_time', 'a:3:{s:5:\"hours\";i:0;s:7:\"minutes\";i:30;s:7:\"seconds\";i:0;}'),
	(3253, 33771, 'cookbook_cook_time', 'a:3:{s:5:\"hours\";i:0;s:7:\"minutes\";i:20;s:7:\"seconds\";i:0;}'),
	(3254, 33771, 'cookbook_inactive_time', 'a:3:{s:5:\"hours\";i:1;s:7:\"minutes\";i:0;s:7:\"seconds\";i:0;}'),
	(3255, 33771, 'cookbook_total_time', 'a:3:{s:5:\"hours\";i:1;s:7:\"minutes\";i:50;s:7:\"seconds\";i:0;}'),
	(3256, 33771, 'cookbook_course', 'Dinner'),
	(3257, 33771, 'cookbook_cuisine', 'Korean, Mexican'),
	(3258, 33771, 'cookbook_ingredients', 'a:2:{s:3:\"raw\";s:480:\"<h4>For the Korean Beef:</h4><ul><li>1 14-ounce can pears, drained (fresh pears work, too)</li><li>1 two-inch knob of fresh ginger</li><li>4 cloves garlic</li><li>1/2 cup soy sauce</li><li>1/2 cup brown sugar</li><li>2 lbs. top sirloin (see notes)</li></ul><h4>For the Tacos:</h4><ul><li>chopped cilantro</li><li>chopped peanuts</li><li>kimchi !!!! or slaw of some <a href=\"https://www.google.com\">sort </a></li><li>sriracha mayo or yum yum sauce</li><li>flour tortillas</li></ul>\";s:6:\"parsed\";a:4:{i:0;a:2:{s:3:\"tag\";s:2:\"h4\";s:7:\"content\";s:20:\"For the Korean Beef:\";}i:2;a:2:{s:3:\"tag\";s:2:\"ul\";s:7:\"content\";a:6:{i:0;s:53:\"1 14-ounce can pears, drained (fresh pears work, too)\";i:1;s:31:\"1 two-inch knob of fresh ginger\";i:2;s:15:\"4 cloves garlic\";i:3;s:17:\"1/2 cup soy sauce\";i:4;s:19:\"1/2 cup brown sugar\";i:5;s:30:\"2 lbs. top sirloin (see notes)\";}}i:4;a:2:{s:3:\"tag\";s:2:\"h4\";s:7:\"content\";s:14:\"For the Tacos:\";}i:6;a:2:{s:3:\"tag\";s:2:\"ul\";s:7:\"content\";a:5:{i:0;s:16:\"chopped cilantro\";i:1;s:15:\"chopped peanuts\";i:2;s:70:\"kimchi !!!! or slaw of some <a href=\"https://www.google.com\">sort </a>\";i:3;s:30:\"sriracha mayo or yum yum sauce\";i:4;s:15:\"flour tortillas\";}}}}'),
	(3259, 33771, 'cookbook_instructions', 'a:2:{s:3:\"raw\";s:631:\"<ol><li><strong>Make the sauce:</strong> Pulse pears, ginger, garlic, soy sauce, and brown sugar until mostly smooth. Reserve half for later use (see notes).</li><li><strong>Cook the meat:</strong> Trim any fat off the meat and cut into a few large chunks (it will shred, but cutting the meat helps it cook faster). Place in an Instant Pot and cover with half of the sauce. Cook on high pressure (manual) for 45 minutes. Release steam and shred the meat in the Instant Pot with two forks.</li><li><strong>Tacos:</strong> Fill each tortilla with beef, cilantro, peanuts, kimchi, and spicy mayo. Oh.My.Gosh. It’s so good.</li></ol>\";s:6:\"parsed\";a:1:{i:0;a:2:{s:3:\"tag\";s:2:\"ol\";s:7:\"content\";a:3:{i:0;s:149:\"<strong>Make the sauce:</strong> Pulse pears, ginger, garlic, soy sauce, and brown sugar until mostly smooth. Reserve half for later use (see notes).\";i:1;s:322:\"<strong>Cook the meat:</strong> Trim any fat off the meat and cut into a few large chunks (it will shred, but cutting the meat helps it cook faster). Place in an Instant Pot and cover with half of the sauce. Cook on high pressure (manual) for 45 minutes. Release steam and shred the meat in the Instant Pot with two forks.\";i:2;s:124:\"<strong>Tacos:</strong> Fill each tortilla with beef, cilantro, peanuts, kimchi, and spicy mayo. Oh.My.Gosh. It’s so good.\";}}}}'),
	(3260, 33771, 'cookbook_notes', '<p><strong>Slow Cooker:</strong> If you want to make this in the slow cooker, follow the same directions but cook the meat on high for 3-5 hours, or low for 6-8 hours.</p><p><strong>Stovetop:</strong> If you want to make this on the stovetop, I recommend slicing the meat thinly and following these directions for super quick and yummy beef.</p><p><strong>Cuts of Meat:</strong> Chuck roast can work, but I found that even with some trimming, it still had too much fat for my liking. Top sirloin is a little more spendy, but I love it in this recipe because there is less fat to work around and you really just get the awesomeness of the beef.</p><p><strong>Marinating:</strong> If you have time to marinate the meat, DO IT. It really enhances the flavor to of the meat to meat to let it all kinda hang out together with the sauce for a while. If you don’t have time, no biggie. I usually don’t have time either, and I’d still give it 5/5 for flavor.</p><p>As written, you’ll have double the sauce you need! So save the remaining sauce (fridge or freezer) for a super fast and easy Round Two of your Korean Beef Tacos. LUCKYYYY.</p><ul><li>What if I have a note in a list?</li><li>Another one!</li></ul>'),
	(3261, 33771, 'cookbook_nutrition', 'a:12:{s:12:\"serving_size\";s:6:\"1 taco\";s:8:\"calories\";d:227;s:5:\"sugar\";d:17;s:6:\"sodium\";d:486;s:13:\"carbohydrates\";d:20;s:5:\"fiber\";d:3;s:7:\"protein\";d:27;s:3:\"fat\";d:4;s:13:\"saturated_fat\";d:1;s:15:\"unsaturated_fat\";d:1;s:9:\"trans_fat\";d:1;s:11:\"cholesterol\";d:67;}'),
	(3262, 33771, 'cookbook_has_rich_markup', '1'),
	(3263, 33771, 'cookbook_embedded_post_ids', 'a:1:{i:0;i:33769;}'),
  (3753, 33912, 'cookbook_author', ''),
  (3754, 33912, 'cookbook_servings', '2'),
  (3755, 33912, 'cookbook_servings_unit', 'slices'),
  (3756, 33912, 'cookbook_prep_time', 'a:3:{s:5:\"hours\";i:0;s:7:\"minutes\";i:20;s:7:\"seconds\";i:0;}'),
  (3757, 33912, 'cookbook_cook_time', 'a:3:{s:5:\"hours\";i:0;s:7:\"minutes\";i:50;s:7:\"seconds\";i:0;}'),
  (3758, 33912, 'cookbook_inactive_time', 'a:3:{s:5:\"hours\";i:0;s:7:\"minutes\";i:0;s:7:\"seconds\";i:0;}'),
  (3759, 33912, 'cookbook_total_time', 'a:3:{s:5:\"hours\";i:1;s:7:\"minutes\";i:10;s:7:\"seconds\";i:0;}'),
  (3760, 33912, 'cookbook_course', 'Dessert'),
  (3761, 33912, 'cookbook_cuisine', ''),
  (3762, 33912, 'cookbook_ingredients', 'a:2:{s:3:\"raw\";s:71:\"<ul><li>1 apple</li><li>1 cup raspberries</li><li>1 pie crust</li></ul>\";s:6:\"parsed\";a:1:{i:0;a:2:{s:3:\"tag\";s:2:\"ul\";s:7:\"content\";a:3:{i:0;s:7:\"1 apple\";i:1;s:17:\"1 cup raspberries\";i:2;s:11:\"1 pie crust\";}}}}'),
  (3763, 33912, 'cookbook_instructions', 'a:2:{s:3:\"raw\";s:29:\"<p>Make pie</p><p>eat pie</p>\";s:6:\"parsed\";a:2:{i:0;a:2:{s:3:\"tag\";s:1:\"p\";s:7:\"content\";s:8:\"Make pie\";}i:2;a:2:{s:3:\"tag\";s:1:\"p\";s:7:\"content\";s:7:\"eat pie\";}}}'),
  (3764, 33912, 'cookbook_notes', ''),
  (3765, 33912, 'cookbook_nutrition', 'a:12:{s:12:\"serving_size\";s:0:\"\";s:8:\"calories\";i:0;s:5:\"sugar\";i:0;s:6:\"sodium\";i:0;s:13:\"carbohydrates\";i:0;s:5:\"fiber\";i:0;s:7:\"protein\";i:0;s:3:\"fat\";i:0;s:13:\"saturated_fat\";i:0;s:15:\"unsaturated_fat\";i:0;s:9:\"trans_fat\";i:0;s:11:\"cholesterol\";i:0;}'),
  (3766, 33912, 'cookbook_has_rich_markup', '1'),
  (3767, 33912, 'cookbook_embedded_post_ids', 'a:1:{i:0;i:33911;}'),
  (3878, 33912, '_edit_lock', '1531250674:1');


/*!40000 ALTER TABLE `wptests_postmeta` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS `wptests_posts`;
CREATE TABLE `wptests_posts` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_author` bigint(20) unsigned NOT NULL DEFAULT '0',
  `post_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_title` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_excerpt` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'publish',
  `comment_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `ping_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `post_password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `post_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `to_ping` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `pinged` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_modified_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content_filtered` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_parent` bigint(20) unsigned NOT NULL DEFAULT '0',
  `guid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `menu_order` int(11) NOT NULL DEFAULT '0',
  `post_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'post',
  `post_mime_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `comment_count` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `post_name` (`post_name`(191)),
  KEY `type_status_date` (`post_type`,`post_status`,`post_date`,`ID`),
  KEY `post_parent` (`post_parent`),
  KEY `post_author` (`post_author`)
) ENGINE=InnoDB AUTO_INCREMENT=33671 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
LOCK TABLES `wptests_posts` WRITE;
/*!40000 ALTER TABLE `wptests_posts` DISABLE KEYS */;

INSERT INTO `wptests_posts` (`ID`, `post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`)
VALUES
	(33771, 2, '2017-11-20 19:10:38', '2017-11-20 19:10:38', '<p>Food truck fusion-style tacos, straight outta the Instant Pot! Flavorful garlic and ginger beef gets tucked into flour tortillas with kimchi, cilantro, peanuts, and spicy mayo. Oooo-ee!</p><p>A <a href=\"https://google.com\">link</a>. <strong>Bold Text</strong>. <em>Italic Text</em>. </p><ul><li>An unordered list item.</li><li>And another one.</li></ul><ol><li>And an ordered list item.</li><li>And another one, for funzies.</li></ol><h4>A Heading!</h4>', 'Instant Pot Korean Beef Tacos', '', 'publish', 'closed', 'closed', '', '33771', '', '', '2017-11-20 19:10:38', '2017-11-20 19:10:38', '', 0, 'urn:uuid:7c83b35a-a839-4586-b766-336b412191bf', 0, 'cookbook_recipe', '', 0),
  (33912, 2, '2018-06-28 18:19:58', '2018-06-28 18:19:58', '', 'Something with Nutrition Info', '', 'publish', 'closed', 'closed', '', '33912', '', '', '2018-06-28 18:19:58', '2018-06-28 18:19:58', '', 0, 'urn:uuid:3678c4a9-b586-4589-be77-6ffcbb047319', 0, 'cookbook_recipe', '', 0);


/*!40000 ALTER TABLE `wptests_posts` ENABLE KEYS */;
UNLOCK TABLES;
