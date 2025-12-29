DROP TABLE IF EXISTS tFriends;
DROP TABLE IF EXISTS tWall;
DROP TABLE IF EXISTS tUser;

CREATE TABLE tUser (
	user_id INT NOT NULL AUTO_INCREMENT,
	name VARCHAR(50) NOT NULL,
	email_id VARCHAR(100) NOT NULL UNIQUE,
	password VARCHAR(255) NOT NULL,
	address VARCHAR(100),
	phone BIGINT,
	PRIMARY KEY (user_id)
) ENGINE=InnoDB;

CREATE TABLE tFriends (
	user_id INT NOT NULL,
	friend_id INT NOT NULL,
	PRIMARY KEY (user_id, friend_id),
	CONSTRAINT fk_friends_user FOREIGN KEY (user_id) REFERENCES tUser(user_id) ON DELETE CASCADE,
	CONSTRAINT fk_friends_friend FOREIGN KEY (friend_id) REFERENCES tUser(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE tWall (
	user_id INT NOT NULL,
	posting_date DATETIME DEFAULT CURRENT_TIMESTAMP,
	post VARCHAR(200) NOT NULL,
	CONSTRAINT fk_wall_user FOREIGN KEY (user_id) REFERENCES tUser(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO tUser (user_id, name, email_id, password, address, phone) VALUES
(1, 'smit', 'smit@gmail.com', '123456', 'Hyderabad', 7043635077),
(2, 'deepak', 'deepak@gmail.com', '123456', 'Chennai', 9876543211),
(3, 'dhruv', 'dhruv@gmail.com', '123456', 'Bangalore', 9876543212);

INSERT INTO tFriends (user_id, friend_id) VALUES
(1, 2),
(1, 3),
(2, 3);

INSERT INTO tWall (user_id, post, posting_date) VALUES
(1, 'Hello, this is Smit!', NOW()),
(2, 'Deepak first post', NOW()),
(3, 'Dhruv enjoying SQL', NOW());