## Sibyl
Sybil is a VK bot that could be used as chat-manager and scorecounter

How to write commands: https://vk.com/page-173076069_54615607

### Installation
Copy files from repo to your folder and then create `datasheets` and `images` folders in it. There is some constants that need to be set in `handler.php`, `oauth.php`, `sevilla/BotInterface` and `sevilla/WebInterface` files. You'll find instructions in it. 
Set your Callback API server address to http://www.example.com/path/to/file/handler.php.

### Database tables structure
If you want to use this bot, you'll need to create some tables in your MySQL database. Tables that are used:

```sql
CREATE TABLE `members` (
    `local_id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `admin` tinyint(1) NOT NULL,
    `balance` int(11) NOT NULL,
    `first_name` varchar(255) COLLATE utf8_general_ci NOT NULL,
    `last_name` varchar(255) COLLATE utf8_general_ci NOT NULL,
    `fname_gen` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
    `lname_gen` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
    `fname_dat` varchar(255) COLLATE utf8_general_ci NOT NULL,
    `lname_dat` varchar(255) COLLATE utf8_general_ci NOT NULL,
    `fname_acc` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
    `lname_acc` varchar(255) COLLATE utf8_general_ci DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
```
```sql
CREATE TABLE `operations` (
  `id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `executor` int(11) NOT NULL,
  `object` int(11) NOT NULL,
  `comment` text COLLATE utf8_unicode_ci NOT NULL,
  `result` int(11) NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `new_balance` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
```
```sql
CREATE TABLE `requests` (
  `request_id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `confirmed` tinyint(1) NOT NULL,
  `moderator_id` int(11) NOT NULL,
  `from_id` int(11) NOT NULL,
  `photos` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
```
