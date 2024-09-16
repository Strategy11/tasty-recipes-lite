DROP TABLE IF EXISTS `wptests_amd_yrecipe_recipes`;

CREATE TABLE `wptests_amd_yrecipe_recipes` (
  `recipe_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) unsigned NOT NULL,
  `recipe_title` text DEFAULT NULL,
  `recipe_image` text DEFAULT NULL,
  `summary` text DEFAULT NULL,
  `rating` text DEFAULT NULL,
  `prep_time` text DEFAULT NULL,
  `cook_time` text DEFAULT NULL,
  `total_time` text DEFAULT NULL,
  `yield` text DEFAULT NULL,
  `serving_size` varchar(50) DEFAULT NULL,
  `calories` varchar(50) DEFAULT NULL,
  `fat` varchar(50) DEFAULT NULL,
  `ingredients` text DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`recipe_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `wptests_amd_yrecipe_recipes` WRITE;
/*!40000 ALTER TABLE `wptests_amd_yrecipe_recipes` DISABLE KEYS */;

INSERT INTO `wptests_amd_yrecipe_recipes` (`recipe_id`, `post_id`, `recipe_title`, `recipe_image`, `summary`, `rating`, `prep_time`, `cook_time`, `total_time`, `yield`, `serving_size`, `calories`, `fat`, `ingredients`, `instructions`, `notes`, `created_at`)
VALUES
	(1,33774,'Instant Pot Korean Beef Tacos','http://tasty.handbuilt.co/wp-content/uploads/2017/11/Korean-BBQ-Beef-Tacos.jpg','Food truck fusion-style tacos, straight outta the Instant Pot! Flavorful garlic and ginger beef gets tucked into flour tortillas with kimchi, cilantro, peanuts, and spicy mayo. Oooo-ee!',NULL,'PT30M','PT20M','PT2H','8 tacos','1 taco','227','4g','!For the Korean Beef:\r\n\r\n1 14-ounce can pears, drained (fresh pears work, too)\r\n1 two-inch knob of fresh ginger\r\n4 cloves garlic\r\n1/2 cup soy sauce\r\n1/2 cup brown sugar\r\n2 lbs. top sirloin (see notes)\r\n\r\n!For the Tacos:\r\n\r\nchopped cilantro\r\nchopped peanuts\r\nkimchi !!!! or slaw of some sort\r\nsriracha mayo or yum yum sauce\r\nflour tortillas\r\n_italic text_\r\n*bold text*\r\nAnd a [link|https://google.com]','Make the sauce: Pulse pears, ginger, garlic, soy sauce, and brown sugar until mostly smooth. Reserve half for later use (see notes).\r\nCook the meat: Trim any fat off the meat and cut into a few large chunks (it will shred, but cutting the meat helps it cook faster). Place in an Instant Pot and cover with half of the sauce. Cook on high pressure (manual) for 45 minutes. Release steam and shred the meat in the Instant Pot with two forks.\r\nTacos: Fill each tortilla with beef, cilantro, peanuts, kimchi, and spicy mayo. Oh.My.Gosh. It\'s so good. There is an image below this instruction\r\n%http://tasty.handbuilt.co/wp-content/uploads/2017/11/Korean-BBQ-Beef-Tacos.jpg','Slow Cooker: If you want to make this in the slow cooker, follow the same directions but cook the meat on high for 3-5 hours, or low for 6-8 hours.\r\n\r\nStovetop: If you want to make this on the stovetop, I recommend slicing the meat thinly and following these directions for super quick and yummy beef.\r\n\r\nCuts of Meat: Chuck roast can work, but I found that even with some trimming, it still had too much fat for my liking. Top sirloin is a little more spendy, but I love it in this recipe because there is less fat to work around and you really just get the awesomeness of the beef.\r\n\r\nMarinating: If you have time to marinate the meat, DO IT. It really enhances the flavor to of the meat to meat to let it all kinda hang out together with the sauce for a while. If you don’t have time, no biggie. I usually don’t have time either, and I’d still give it 5/5 for flavor.\r\n\r\nAs written, you’ll have double the sauce you need! So save the remaining sauce (fridge or freezer) for a super fast and easy Round Two of your Korean Beef Tacos. LUCKYYYY.','2017-11-20 11:17:05');

/*!40000 ALTER TABLE `wptests_amd_yrecipe_recipes` ENABLE KEYS */;
UNLOCK TABLES;
