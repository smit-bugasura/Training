```sql
CREATE TABLE tUser (
    user_id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    email_id VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    address VARCHAR(100),
    phone BIGINT,
    PRIMARY KEY (user_id)
);
```
```bash
DESC tUser;
+----------+--------------+------+-----+---------+----------------+
| Field    | Type         | Null | Key | Default | Extra          |
+----------+--------------+------+-----+---------+----------------+
| user_id  | int          | NO   | PRI | NULL    | auto_increment |
| name     | varchar(50)  | NO   |     | NULL    |                |
| email_id | varchar(100) | NO   | UNI | NULL    |                |
| password | varchar(255) | NO   |     | NULL    |                |
| address  | varchar(100) | YES  |     | NULL    |                |
| phone    | bigint       | YES  |     | NULL    |                |
+----------+--------------+------+-----+---------+----------------+
6 rows in set (0.00 sec)
```

```sql
CREATE TABLE tFriends (
    user_id INT NOT NULL,
    friend_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES tUser(user_id),
    FOREIGN KEY (friend_id) REFERENCES tUser(user_id)
);
```

```bash
DESC tFriends;
+-----------+------+------+-----+---------+-------+
| Field     | Type | Null | Key | Default | Extra |
+-----------+------+------+-----+---------+-------+
| user_id   | int  | NO   | MUL | NULL    |       |
| friend_id | int  | NO   | MUL | NULL    |       |
+-----------+------+------+-----+---------+-------+
2 rows in set (0.01 sec)
```

```sql
CREATE TABLE tWall (
    user_id INT NOT NULL,
    posting_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    post VARCHAR(200) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES tUser(user_id)
);
```

```bash
DESC tWall;
+--------------+--------------+------+-----+-------------------+-------------------+
| Field        | Type         | Null | Key | Default           | Extra             |
+--------------+--------------+------+-----+-------------------+-------------------+
| user_id      | int          | NO   | MUL | NULL              |                   |
| posting_date | datetime     | YES  |     | CURRENT_TIMESTAMP | DEFAULT_GENERATED |
| post         | varchar(200) | NO   |     | NULL              |                   |
+--------------+--------------+------+-----+-------------------+-------------------+
3 rows in set (0.00 sec)
```

```sql
INSERT INTO tUser (user_id, name, email_id, password, address, phone) VALUES
(1, 'smit', 'smit@gmail.com', '123456', 'Hyderabad', 7043635077),
(2, 'deepak',  'deepak@gmail.com',  '123456', 'Chennai',   9876543211),
(3, 'dhruv','dhruv@gmail.com','123456', 'Bangalore', 9876543212);
```

```bash
SELECT * FROM tUser;
+---------+--------+------------------+----------+-----------+------------+
| user_id | name   | email_id         | password | address   | phone      |
+---------+--------+------------------+----------+-----------+------------+
|       1 | smit   | smit@gmail.com   | 123456   | Hyderabad | 7043635077 |
|       2 | deepak | deepak@gmail.com | 123456   | Chennai   | 9876543211 |
|       3 | dhruv  | dhruv@gmail.com  | 123456   | Bangalore | 9876543212 |
+---------+--------+------------------+----------+-----------+------------+
3 rows in set (0.00 sec)
```

```sql
INSERT INTO tFriends (user_id, friend_id) VALUES
(1, 2),
(1, 3);
(1, 2),
(2, 3);
```
```bash
SELECT * FROM tFriends;
+---------+-----------+
| user_id | friend_id |
+---------+-----------+
|       1 |         2 |
|       1 |         3 |
|       1 |         2 |
|       2 |         3 |
+---------+-----------+
4 rows in set (0.00 sec)
```

```sql
INSERT INTO tWall (user_id, post, posting_date) VALUES
(1, 'Hello, this is Smit!', NOW()),
(2, 'Deepak first post', NOW()),
(3, 'Dhruv enjoying SQL', NOW());
```

```bash
SELECT * FROM tWall;
+---------+---------------------+----------------------+
| user_id | posting_date        | post                 |
+---------+---------------------+----------------------+
|       1 | 2025-12-17 22:00:30 | Hello, this is Smit! |
|       2 | 2025-12-17 22:00:30 | Deepak first post    |
|       3 | 2025-12-17 22:00:30 | Dhruv enjoying SQL   |
+---------+---------------------+----------------------+
3 rows in set (0.01 sec)
```
### Write a query to fetch all information for a person given his name.

```sql
SELECT 
    u.user_id,
    u.name,
    u.email_id,
    u.address,
    u.phone,
    f.friend_id,
    fu.name AS friend_name,
    w.post,
    w.posting_date
FROM tUser u
LEFT JOIN tFriends f 
    ON u.user_id = f.user_id
LEFT JOIN tUser fu 
    ON fu.user_id = f.friend_id
LEFT JOIN tWall w 
    ON w.user_id = u.user_id
WHERE u.name = 'smit';
```

```bash
+---------+------+----------------+-----------+------------+-----------+-------------+----------------------+---------------------+
| user_id | name | email_id       | address   | phone      | friend_id | friend_name | post                 | posting_date        |
+---------+------+----------------+-----------+------------+-----------+-------------+----------------------+---------------------+
|       1 | smit | smit@gmail.com | Hyderabad | 7043635077 |         2 | deepak      | Hello, this is Smit! | 2025-12-17 22:00:30 |
|       1 | smit | smit@gmail.com | Hyderabad | 7043635077 |         3 | dhruv       | Hello, this is Smit! | 2025-12-17 22:00:30 |
+---------+------+----------------+-----------+------------+-----------+-------------+----------------------+---------------------+
2 rows in set (0.00 sec)
```

### Write a query to fetch all posts of a person given his name

```sql
SELECT 
    u.name,
    w.post,
    w.posting_date
FROM tUser u
LEFT JOIN tWall w 
    ON u.user_id = w.user_id
WHERE u.name = 'deepak';
```

```bash
+--------+-------------------+---------------------+
| name   | post              | posting_date        |
+--------+-------------------+---------------------+
| deepak | Deepak first post | 2025-12-17 22:00:30 |
+--------+-------------------+---------------------+
1 row in set (0.00 sec)
```

### Write a query to fetch all posts of a particular friend of a person, given his name and the friends name.

```sql
SELECT 
    friend.name AS friend_name,
    w.post,
    w.posting_date
FROM tUser u
JOIN tFriends f 
    ON (u.user_id = f.user_id OR u.user_id = f.friend_id)
JOIN tUser friend 
    ON friend.user_id = CASE 
        WHEN f.user_id = u.user_id THEN f.friend_id
        ELSE f.user_id
    END
JOIN tWall w 
    ON w.user_id = friend.user_id
WHERE u.name = 'smit'
  AND friend.name = 'deepak';
```

```bash
+-------------+-------------------+---------------------+
| friend_name | post              | posting_date        |
+-------------+-------------------+---------------------+
| deepak      | Deepak first post | 2025-12-17 22:00:30 |
+-------------+-------------------+---------------------+
1 row in set (0.00 sec)
```

### Write a query to fetch all friends of a particular friend of a person, given the persons name and friend's name.

```sql
SELECT 
    fof.name AS friend_of_friend
FROM tUser u
JOIN tFriends f1
    ON u.user_id = f1.user_id
JOIN tUser friend
    ON friend.user_id = f1.friend_id
JOIN tFriends f2
    ON friend.user_id = f2.user_id
JOIN tUser fof
    ON fof.user_id = f2.friend_id
WHERE u.name = 'smit'
  AND friend.name = 'deepak'
  AND fof.user_id <> u.user_id;
```

```bash
+------------------+
| friend_of_friend |
+------------------+
| dhruv            |
| dhruv            |
+------------------+
2 rows in set (0.00 sec)
```

### Write a query to remove a particular friend from a persons list, given the persons name

```sql
DELETE f
FROM tFriends f
JOIN tUser u 
    ON u.user_id = f.user_id
JOIN tUser friend
    ON friend.user_id = f.friend_id
WHERE u.name = 'smit'
  AND friend.name = 'dhruv';
```

```bash
SELECT * FROM tFriends;
+---------+-----------+
| user_id | friend_id |
+---------+-----------+
|       1 |         2 |
|       1 |         2 |
|       2 |         3 |
+---------+-----------+
3 rows in set (0.00 sec)
```

### Write a query to post something on his wall

```sql
INSERT INTO tWall (user_id, post, posting_date)
SELECT 
    u.user_id,
    'This is my first wall post',
    CURRENT_TIMESTAMP
FROM tUser u
WHERE u.name = 'smit';
```

```bash
SELECT * FROM tWall;
+---------+---------------------+----------------------------+
| user_id | posting_date        | post                       |
+---------+---------------------+----------------------------+
|       1 | 2025-12-17 22:00:30 | Hello, this is Smit!       |
|       2 | 2025-12-17 22:00:30 | Deepak first post          |
|       3 | 2025-12-17 22:00:30 | Dhruv enjoying SQL         |
|       1 | 2025-12-17 22:24:21 | This is my first wall post |
+---------+---------------------+----------------------------+
4 rows in set (0.00 sec)
```