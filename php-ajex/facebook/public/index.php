<?php
// Start the session to manage user login state
session_start();

// Include the database configuration file
require_once "../config/database.php";

date_default_timezone_set('Asia/Kolkata');

// Initialize variables for login status and user data
$loggedIn = false;
$loggedUser = null;

// Check if the user is logged in by verifying the session user_id
if (isset($_SESSION['user_id'])) {
	$loggedIn = true;
	$loggedUser = [
		'user_id' => $_SESSION['user_id']
	];
}

// If no active session, send the user back to the login page
if (!$loggedIn) {
	header('Location: login.php');
	exit;
}

// Fetch all users from the database
$stmt = $conn->query("SELECT * FROM tUser");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get the profile id from the URL parameter, if present
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);


// If the user is logged in, fetch the full user record for the current user
$userFull = null;
if ($loggedIn) {
	$uStmt = $conn->prepare('SELECT * FROM tUser WHERE user_id = :id LIMIT 1');
	$uStmt->execute([':id' => $loggedUser['user_id']]);
	$userFull = $uStmt->fetch(PDO::FETCH_ASSOC);

	// check for profile id in parameter
	if ($id) {
		$pStmt = $conn->prepare('SELECT * FROM tUser WHERE user_id = :id LIMIT 1');
		$pStmt->execute([':id' => $id]);
		$profileFull = $pStmt->fetch(PDO::FETCH_ASSOC);
		if (!$profileFull) {
			// remove parameter and redirect index.php
			header('Location: index.php');
			exit;
		}
	}
}

// list all posts
if (!$id) {
	$pStmt = $conn->prepare("SELECT tWall.*, tUser.name FROM tWall JOIN tUser ON tWall.user_id = tUser.user_id WHERE tWall.user_id = :id OR tWall.user_id IN (SELECT friend_id FROM tFriends WHERE user_id = :id) ");
	$pStmt->execute([':id' => $loggedUser['user_id']]);
	$posts = $pStmt->fetchAll(PDO::FETCH_ASSOC);
} else {
	$posts = $conn->prepare('SELECT * FROM tWall WHERE user_id = :id');
	$posts->execute([':id' => $id]);
	$posts = $posts->fetchAll(PDO::FETCH_ASSOC);
}
// list all friends
if (!$id) {
	$fStmt = $conn->prepare("SELECT tUser.* FROM tUser JOIN tFriends ON tUser.user_id = tFriends.friend_id WHERE tFriends.user_id = :id");
	$fStmt->execute([":id" => $loggedUser["user_id"]]);
	$friendList = $fStmt->fetchAll(PDO::FETCH_ASSOC);
} else {
	$friendList = $conn->prepare("SELECT tUser.* FROM tUser JOIN tFriends ON tUser.user_id = tFriends.friend_id WHERE tFriends.user_id = :id");
	$friendList->execute([":id" => $id]);
	$friendList = $friendList->fetchAll(PDO::FETCH_ASSOC);
}


// Handle new post form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['preText'])) {
	$postText = trim($_POST['preText']);
	if ($postText) {
		$userId = $_SESSION['user_id'];
		$postingDate = date('Y-m-d H:i:s');
		$stmt = $conn->prepare("INSERT INTO tWall (user_id, post, posting_date) VALUES (?, ?, ?)");
		$stmt->execute([$userId, $postText, $postingDate]);
		echo "Post added successfully";

	} else {
		echo "Empty post";
	}
	exit;
}

// Handle profile update form submission
if (
	$_SERVER["REQUEST_METHOD"] === "POST" &&
	(
		!empty($_POST["preProfileName"]) ||
		!empty($_POST["preProfileEmail"]) ||
		!empty($_POST["preProfilePassword"]) ||
		!empty($_POST["preProfileAddress"]) ||
		!empty($_POST["preProfilePhone"])
	)
) {
	$name = trim($_POST["preProfileName"]);
	$email = trim($_POST["preProfileEmail"]);
	$password = trim($_POST["preProfilePassword"]);
	$address = trim($_POST["preProfileAddress"]);
	$phone = trim($_POST["preProfilePhone"]);

	$userId = $_SESSION['user_id'];

	$updateFields = [];
	$params = [':id' => $userId];

	// Prepare the fields to be updated based on provided input
	if ($name) {
		$updateFields[] = "name = :name";
		$params[':name'] = $name;
	}
	if ($email) {
		$updateFields[] = "email_id = :email_id";
		$params[':email_id'] = $email;
	}
	if ($password) {
		$updateFields[] = "password = :password";
		$params[':password'] = $password;
	}

	if ($address) {
		$updateFields[] = "address = :address";
		$params[':address'] = $address;
	}

	if ($phone) {
		$updateFields[] = "phone = :phone";
		$params[':phone'] = $phone;
	}

	// Perform the update if there are fields to update
	if (!empty($updateFields)) {
		$sql = "UPDATE tUser SET " . implode(", ", $updateFields) . " WHERE user_id = :id";
		$stmt = $conn->prepare($sql);
		$stmt->execute($params);
		echo "Profile updated successfully";
	} else {
		echo "No fields to update";
	}
	exit;
}

?>
<!-- Begin HTML output for the Facebook homepage -->
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<!-- fav icon -->
	<link rel="icon" type="image/ico" href="./images/favicon.ico">
	<title>(20+) Facebook</title>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
	<link rel="stylesheet" href="./styles/index.css">
</head>

<body>
	<!-- Main navigation bar -->
	<navbar class="navbar">
		<!-- Search section with logo and input field -->
		<div class="search">
			<img src="images/facebook_logo.svg" alt="logo" class="logo">
			<input type="text" class="fb-search" placeholder="Search Facebook">
		</div>
		<!-- Navigation tabs in the middle (Home, Watch, Marketplace, Gaming) -->
		<div class="middle">
			<span class="edit_styles"><img src="./images/home_tab.svg" alt=""></span>
			<span class="edit_styles"><img src="./images/reel-tab.svg" alt=""></span>
			<span class="edit_styles"><img src="./images/marketplace_tab.svg" alt=""></span>
			<span class="edit_styles"><img src="./images/gaming_tab.svg" alt=""></span>
		</div>
		<!-- User profile and action icons on the right -->
		<div class="profile-details">
			<!-- Menu, messenger, and notifications icons -->
			<span class="end">
				<img src="./images/menu_icon.svg" alt="menu_icon" class="last">
			</span>
			<span class="end">
				<img src="./images/messenger_icon.svg" alt="message_icon" class="last">
			</span>
			<span class="end">
				<img src="./images/notifications_icon.svg" alt="notification_icon" class="last">
			</span>
			<!-- User profile dropdown -->
			<span class="profiles profile-wrapper end">
				<img src="./images/user_<?php echo $_SESSION['user_id'] ?>.jpg" alt="user_profile_pic" class="right-icons profile-icon" height="40" width="40">
				<img src="./images/dropdown_icon.svg" class="drop-icon">
				<!-- Dropdown menu items -->
				<ul class="dropdown-menu-profile" id="dropdown-menu">
					<div class="user-name-section" id="profile_edit">
						<img src="./images/user_<?php echo $_SESSION['user_id'] ?>.jpg" alt="user profile" class="dropdown-profile-icon" height="40" width="40">
						<div class="user-name-role">
							<span class="user-name"><?php echo ucfirst($userFull['name']); ?></span>
						</div>
					</div>
					<li>
						<div class="link-icon">
							<img src="./images/privacy-icon.svg" alt="">
						</div>
						<a href="#">Settings & privacy</a>
					</li>
					<li>
						<div class="link-icon">
							<img src="./images/help-support.svg" width="20" height="20" alt="Help & support">
						</div>
						<a href="#">Help & support</a>
					</li>
					<li>
						<div class="link-icon">
							<img src="./images/display-accessibility.svg" width="20" height="20" alt="Display & accessibility">
						</div>
						<a href="#">Display & accessibility</a>
					</li>
					<li>
						<div class="link-icon">
							<i class="logout" data-visualcompletion="css-img" aria-hidden="true"></i>
						</div>
						<a href="logout.php">Log out</a>
					</li>
				</ul>
			</span>
		</div>
	</navbar>
	<!-- Main content section -->
	<div class="body-section">
		<div class="gradiant-bg">
			<section class="main-section">
				<div class="profile-banner-image">
					<img src="./images/llama.jpg" alt="background_img" class="banner-image" loading="eager">
				</div>
				<!-- Profile information section -->
				<div class="profile-info">
					<div class="info-section">
						<div class="profile-image">
							<img height="100%" src="./images/<?php if ($profileFull) { echo "user_" . $profileFull['user_id'] . '.jpg'; } else { echo "mark-zuck.jpg"; } ?>" width="100%" alt="Profile Picture">
						</div>
						<div class="profile-content">
							<div class="name-follow">
								<div class="left-profile-content">
									<div class="row-content">
										<div class="col-content">
											<h1>
												<?php if ($profileFull) { echo ucfirst($profileFull['name']); } else { echo 'Mark Zuckerberg'; } ?>
											</h1>
											<span class="verfiy-icon">
												<img src="./images/verified.svg" width="16" height="16" alt="Verified account">
											</span>
										</div>
										<div class="col-content">
											<span class="followers-count">120M followers</span>
										</div>
									</div>
								</div>
								<div class="right-profile-content">
									<div class="follow-button">
										<img src="./images/follow-icon.png" alt="follow-icon" aria-hidden="true" height="16" width="16">
										<span>Follow</span>
									</div>
									<div class="search-button">
										<img src="./images/search-icon.png" alt="search-icon" aria-hidden="true" height="16" width="16">
										<span>Search</span>
									</div>
									<div class="dropdown-button">
										<img src="./images/dropdown-arrow.svg" width="16" height="16" alt="Dropdown">
									</div>
								</div>
							</div>
							<div class="profile-tagline">
								<span>Bringing the world closer together.</span>
							</div>
							<!-- Personal information section-->
							<div class="ed-office-content">
								<ul>
									<li>
										<span><img height="12" width="12" src="./images/public-figure.svg" width="12" alt=""></span> Public figure
									</li>
									<span aria-hidden="true">·</span>
									<li>
										<span><img height="12" width="12" src="./images/location-pin.svg" width="12" alt=""></span> Palo Alto, California
									</li>
									<span aria-hidden="true">·</span>
									<li>
										<span><img height="12" width="12" src="./images/office-building.svg" width="12" alt=""></span> Meta
									</li>
									<span aria-hidden="true">·</span>
									<li>
										<span><img height="12" width="12" src="./images/education.svg" width="12" alt=""></span> Harvard University
									</li>
								</ul>
							</div>
							<!-- Friends list section -->
							<div class="follower-list">
								<ul>
									<li><img src="./images/follow1.jpg" alt="follow1" style="--index: 1;"></li>
									<li><img src="./images/follow2.jpg" alt="follow2" style="--index: 2;"></li>
									<li><img src="./images/follow3.jpg" alt="follow3" style="--index: 3;"></li>
									<li><img src="./images/follow4.jpg" alt="follow4" style="--index: 4;"></li>
									<li><img src="./images/follow5.jpg" alt="follow5" style="--index: 5;"></li>
									<li><img src="./images/follow6.jpg" alt="follow6" style="--index: 6;"></li>
									<li><img src="./images/follow7.jpg" alt="follow7" style="--index: 7;"></li>
									<li><img src="./images/follow8.jpg" alt="follow8" style="--index: 8;"></li>
									<li><img src="./images/follow9.jpg" alt="follow9" style="--index: 9;"></li>
									<li><img src="./images/follow10.png" alt="follow10" style="--index: 10;"></li>
									<li><img src="./images/follow11.jpg" alt="follow11" style="--index: 11;"></li>
									<li><img src="./images/follow9.jpg" alt="follow9" style="--index: 12;"></li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</section>
		</div>
		<div class="stkcy-tab">
			<div class="profile-bottom-tab">
				<div class="left-profile-bottom-tab">
					<span id="all-tab" class="active-tab">All</span>
					<span id="about-tab">About</span>
					<span>Photos</span>
					<span id="friends-tab">Friends</span>
					<span>Reels</span>
					<span class="more-span">
						More&nbsp;
						<img src="./images/more-dropdown.svg" width="16" height="16" alt="More">
					</span>
				</div>
				<div class="right-profile-bottom-tab">
					<span>
						<img src="./images/three-dots.svg" width="16" height="16" alt="More options">
					</span>
				</div>
			</div>
		</div>
		<div class="post-section-body">
			<div class="post-section">
				<!-- Post Section left Part -->
				<div class="post-section-left">
					<div class="personal-details">
						<h3>Personal details</h3>
						<div class="loaction-details personal-details-card">
							<img height="24" src="./images/location-outline.svg" width="24" alt="location">
							<span>Lives in Palo Alto, California</span>
						</div>
						<div class="loaction-details personal-details-card">
							<img height="24" src="./images/home.svg" width="24" alt="home">
							<span>From Dobbs Ferry, New York</span>
						</div>
						<div class="loaction-details personal-details-card">
							<img height="24" src="./images/birthday.svg" width="24" alt="birthday">
							<span>May 14, 1984</span>
						</div>
						<div class="see-more">See more personal details</div>
						<br>
						<h3 class="communities">Communities</h3>
						<div class="loaction-details personal-details-card meta-card">
							<img class="meta-img" height="24" src="./images/channel.svg" width="24" alt="channel">
							<span>Meta Channel
							</span>
						</div>
						<div class="communiti-text">Channel · 802K members</div>
						<br>
						<h3>Work</h3>
						<div class="loaction-details personal-details-card work-details-card">
							<div class="left-personal-details-card">
								<img class="work-logo" src="./images/meta.jpg" height="40" width="40" alt="meta-logo">
							</div>
							<div class="right-personal-details-card">
								<span class="work-place">Meta</span>
								<span class="work-position">Founder and CEO</span>
								<span class="work-duration">Feb 4, 2004 - Present . 21 years, 10 months</span>
							</div>
						</div>
						<div class="work-see-more">See more work</div>
						<br>
						<h3>Education</h3>
						<div class="loaction-details personal-details-card work-details-card">
							<div class="left-personal-details-card">
								<img class="work-logo" src="./images/harvard.jpg" height="40" width="40" alt="harvard-logo">
							</div>
							<div class="right-personal-details-card">
								<span class="work-place">Harvard University</span>
								<span class="work-duration">August 30, 2002 - April 30, 2004</span>
							</div>
						</div>
						<div class="work-see-more">See more education</div>
					</div>
					<div class="photos-details">
						<div class="photos-title">
							<h3>Photos</h3>
							<a href="#">See all photos</a>
						</div>
						<div class="photos-grid">
							<img src="./images/grid1.jpg" alt="Description 1">
							<img src="./images/grid2.jpg" alt="Description 2">
							<img src="./images/grid3.jpg" alt="Description 3">
							<img src="./images/grid4.jpg" alt="Description 4">
							<img src="./images/grid5.jpg" alt="Description 5">
							<img src="./images/grid6.jpg" alt="Description 6">
							<img src="./images/grid7.jpg" alt="Description 7">
							<img src="./images/grid8.jpg" alt="Description 8">
							<img src="./images/grid9.jpg" alt="Description 9">
						</div>
					</div>
				</div>
				<div class="posts-content">
					<div class="post-filter-card">
						<h2>Posts</h2>
						<!-- Filter dropdown-->
						<span class="post-filter-dropdown">
							<img src="./images/filters.svg" width="16" height="16" alt="Filters">
							<span>Filters</span>
						</span>
					</div>
					<?php if (!$id) { ?>
						<div class="post-form-card">
							<!-- simple one textarea and submit button -->
							<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
								<div class="post-form-card-header">
									<div class="left-post-form-card-header">
										<div class="post-profile-image">
											<img src="./images/user_<?php echo $_SESSION['user_id'] ?>.jpg" alt="Profile Picture">
										</div>
									</div>
									<div class="right-post-form-card-header">
										<input type="text" name="post-text" class="post-text-input"
											placeholder="What's on your mind, <?php echo ucfirst($userFull['name']); ?>">
									</div>
								</div>
								<div class="submit-btn">
									<button id="post-form" type="submit" name="post" disabled>Post</button>
								</div>
							</form>
						</div>
					<?php } ?>
					<!-- Posts section current-->
					<div class="currunt-post" id="currunt-post"></div>
					<?php
					if ($posts)
						foreach ($posts as $post) {
							$date_time = date("F j \\a\\t g:i A", strtotime($post['posting_date']));
							if ($id) {
								$user_name = ucfirst($profileFull['name']);
							} else {
								$user_name = ucfirst($post['name']);
							}
							echo <<<_POST
									<div class="post-card">
										<div class="post-card-header">
											<div class="left-post-card-header">
												<div class="post-profile-image">
													<img src="./images/user_{$post['user_id']}.jpg"
														alt="Profile Picture">
												</div>
												<div class="post-profile-name">
													<span>{$user_name} </span>
													<span>{$date_time} <span aria-hidden="true"> · </span> <img src="./images/public-globe.svg" width="12" height="12" alt="Shared with Public"></span>
												</div>
											</div>
											<div class="right-post-card-header">
												<img src="./images/menu-dots.svg" width="20" height="20" alt="Post menu">
											</div>
							
										</div>
										<div class="post-content">
											<div>{$post['post']}</div>
										</div>
										<div class="post-likes-comments-share">
											<div class="left-post-likes">
												<div class="img-reactes">
													<img height="18" role="presentation" width="18"
														src="./images/reaction-like.svg">
													<img height="18" role="presentation" width="18"
														src="./images/reaction-love.svg">
												</div> 195K
											</div>
											<div class="right-post-likes">
												<span>1.2M comments</span>
												<span>34K shares</span>
												<span>9.6M views</span>
											</div>
										</div>
										<div class="post-like-comments-share-btn">
											<div class="like-btn">
												<i data-visualcompletion="css-img" ></i>
												<span>Like</span>
											</div>
											<div class="comment-btn">
												<i data-visualcompletion="css-img"></i>
												<span>Comment</span>
											</div>
											<div class="share-btn1">
												<i data-visualcompletion="css-img"></i>
												<span>Share</span>
											</div>
										</div>
									</div>
									_POST;
						}
					;

					?>
					<?php if (!$id) { ?>
						<div class="post-card">
							<div class="post-card-header">
								<div class="left-post-card-header">
									<div class="post-profile-image">
										<img src="./images/mark-zuck.jpg" alt="Profile Picture">
									</div>
									<div class="post-profile-name">
										<span>Mark Zuckerberg <img src="./images/verified.svg" width="12" height="12" alt="Verified account"></span>
										<span>December 16 at 10:58 PM <span aria-hidden="true"> · </span>
											<img src="./images/public.svg" width="12" height="12" alt="Shared with Public">
										</span>
									</div>
								</div>
								<div class="right-post-card-header">
									<img src="./images/post-menu.svg" width="20" height="20" alt="Post menu">
								</div>
							</div>
							<div class="post-content">
								<div>10/10 song choice from the new Spotify feature on
									my Oakley Meta glasses
								</div>
								<div class="post-img">
									<img src="./images/post1.jpg" alt="Post Image">
								</div>
							</div>
							<div class="post-likes-comments-share">
								<div class="left-post-likes">
									<div class="img-reactes">
										<img height="18" role="presentation" width="18" src="./images/reaction-like.svg">
										<img height="18" role="presentation" width="18" src="./images/reaction-love.svg">
									</div>
									195K
								</div>
								<div class="right-post-likes">
									<span>1.2M comments</span>
									<span>34K shares</span>
									<span>9.6M views</span>
								</div>
							</div>
							<div class="post-like-comments-share-btn">
								<div class="like-btn">
									<i data-visualcompletion="css-img"></i>
									<span>Like</span>
								</div>
								<div class="comment-btn">
									<i data-visualcompletion="css-img"></i>
									<span>Comment</span>
								</div>
								<div class="share-btn1">
									<i data-visualcompletion="css-img"></i>
									<span>Share</span>
								</div>
							</div>
						</div>
						<div class="post-card">
							<div class="post-card-header">
								<div class="left-post-card-header">
									<div class="post-profile-image">
										<img src="./images/mark-zuck.jpg" alt="Profile Picture">
									</div>
									<div class="post-profile-name">
										<span>
											Mark Zuckerberg
											<img src="./images/verified.svg" width="12" height="12" alt="Verified account">
										</span>
										<span>
											December 16 at 10:58 PM <span aria-hidden="true"> · </span>
											<img src="./images/public.svg" width="12" height="12" alt="Shared with Public">
										</span>
									</div>
								</div>
								<div class="right-post-card-header">
									<img src="./images/post-menu.svg" width="20" height="20" alt="Post menu">
								</div>
							</div>
							<div class="post-content">
								<div>10/10 song choice from the new Spotify feature on my Oakley Meta glasses</div>
								<div class="post-img">
									<img src="./images/post1.jpg" alt="Post Image">
								</div>
							</div>
							<div class="post-likes-comments-share">
								<div class="left-post-likes">
									<div class="img-reactes">
										<img height="18" role="presentation" width="18" src="./images/reaction-like.svg">
										<img height="18" role="presentation" width="18" src="./images/reaction-love.svg">
									</div>
									195K
								</div>
								<div class="right-post-likes">
									<span>1.2M comments</span>
									<span>34K shares</span>
									<span>9.6M views</span>
								</div>
							</div>
							<div class="post-like-comments-share-btn">
								<div class="like-btn">
									<i data-visualcompletion="css-img"></i>
									<span>Like</span>
								</div>
								<div class="comment-btn">
									<i data-visualcompletion="css-img"></i>
									<span>Comment</span>
								</div>
								<div class="share-btn1">
									<i data-visualcompletion="css-img"></i>
									<span>Share</span>
								</div>
							</div>
						</div>
					<?php } ?>
				</div>
			</div>
			<div class="about-section hide">
				<div class="about-content">
					<div class="left-about-content">
						<h3>About</h3>
						<ul>
							<li class="active">Intro</li>
							<li>Category</li>
							<li>Personal details</li>
							<li>Work</li>
							<li>Education</li>
							<li>Privacy and legal info</li>
						</ul>
					</div>
					<div class="right-about-content">
						<div class="intro-section">
							<h4>Bio</h4>
							<div>
								<img height="24" width="24" alt="" src="./images/handwave.svg">
								<p>Bringing the world closer together.</p>
							</div>
						</div>
						<div class="category-section hide" >
							<h4>Category</h4>
							<div>
								<img height="24" width="24" alt="" src="./images/reels.svg">
								<p>Public figure</p>
							</div>
						</div>
						<div class="personal-details-section hide">
							<h4>Personal details</h4>
						</div>
						<div class="work-section hide">
							<h4>Work</h4>
						</div>
						<div class="education-section hide">
							<h4>Education</h4>
						</div>
						<div class="privacy-and-legal-info-section hide">
							<h4>Privacy and legal info</h4>
						</div>
					</div>
				</div>
			</div>
			<div class="friends-section hide">
				<div class="friends-content">
					<div class="friends-content-header">
						<h3>Friends</h3>
						<!-- Search bar -->
						<div class="search">
							<input type="text" class="fb-search" id="friends-search" placeholder="Search">
						</div>
					</div>
					<div id="my-friends-list" class="friends-list">
						<?php foreach ($friendList as $friend): ?>
							<a href="?id=<?php echo $friend['user_id'] ?>">
								<div class="friend-card">
									<div class="friend-profile-image">
										<img src="./images/user_<?php echo $friend['user_id'] ?>.jpg" alt="Profile Picture">
									</div>
									<div class="friend-profile-name">
										<span><?php echo htmlspecialchars(ucfirst($friend['name'])) ?></span>
									</div>
								</div>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<dialog class="model" id="model_edit" closedby="any">
		<!-- Form to Edit User Profile to edit name, email_id, password, address, phone-->
		<form class="edit-profile-form" method="post" autocomplete="off"
			action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
			<h2>Edit Profile</h2>
			<!-- Name, Email, Password, Address, Phone-->
			<label for="name">Name:</label>
			<input type="text" id="name" name="name" autocomplete="off" value="<?php echo htmlspecialchars($userFull['name']); ?>">

			<label for="email">Email:</label>
			<input type="email" id="email" name="email" autocomplete="off" value="<?php echo htmlspecialchars($userFull['email_id']); ?>">

			<label for="password">New Password:</label>
			<input type="password" id="password" name="password" autocomplete="new-password" value="<?php echo htmlspecialchars($userFull['password']); ?>" required>

			<label for="address">Address:</label>
			<input type="text" id="address" name="address" autocomplete="off" value="<?php echo htmlspecialchars($userFull['address']); ?>" required>

			<label for="phone">Phone:</label>
			<input type="text" id="phone" name="phone" autocomplete="off" value="<?php echo htmlspecialchars($userFull['phone']); ?>" required>

			<div class="model-buttons">
				<button id="profile_save" type="submit" name="update_profile">Save Changes</button>
			</div>
		</form>

	</dialog>
	</div>
	<script>
		$(document).ready(function () {
			$('#profile_edit').click(function () {
				var modelEdit = document.getElementById('model_edit');
				modelEdit.showModal();
				// disable scroll bar
				document.body.style.overflow = 'hidden';
				// enable scroll bar on close
				modelEdit.addEventListener('close', function () {
					document.body.style.overflow = '';
				});

			});

			// AJAX form submit handler
			$('#profile_save').click(function () {
				// Get and trim the value to check it
				var profileName = $('#name').val().trim();
				var profileEmail = $('#email').val().trim();
				var profilePassword = $('#password').val().trim();
				var profileAddress = $('#address').val().trim();
				var profilePhone = $('#phone').val().trim();
				console.log(profileName, profileEmail, profilePassword, profileAddress, profilePhone);


				if (profileName && profileEmail && profilePassword && profileAddress && profilePhone) {
					var dataString = 'preProfileName=' + encodeURIComponent(profileName) +
						'&preProfileEmail=' + encodeURIComponent(profileEmail) +
						'&preProfilePassword=' + encodeURIComponent(profilePassword) +
						'&preProfileAddress=' + encodeURIComponent(profileAddress) +
						'&preProfilePhone=' + encodeURIComponent(profilePhone);

					// AJAX request to submit the form data
					$.ajax({
						type: "POST",
						url: window.location.href, // Explicitly sends to the current page URL
						data: dataString,
						cache: false,
						success: function (response) {
							var modelEdit = document.getElementById('model_edit');
							modelEdit.close();


						}
					});
				}
				return false;
			});

			var userName = "<?php echo htmlspecialchars($user_name) ?>";

			$('.profile-wrapper').click(function () {
				$('#dropdown-menu').toggle('slide');
			});
			$('#currunt-post').hide();
			// enable submit button when input is not empty
			$('.post-text-input').on('input', function () {
				if ($(this).val().trim() !== '') {
					$('#post-form').prop('disabled', false);
				} else {
					$('#post-form').prop('disabled', true);
				}
			});

			// form submit handler
			$('#post-form').click(function () {
				// Get and trim the value to check it
				var posttext = $('.post-text-input').val().trim();

				if (posttext) {
					var dataString = 'preText=' + encodeURIComponent(posttext);

					$.ajax({
						type: "POST",
						url: window.location.href, // Explicitly sends to the current page URL
						data: dataString,
						cache: false,
						success: function (response) {
							$('#currunt-post').prepend(`<div class="post-card">
							<div class="post-card-header">
								<div class="left-post-card-header">
									<div class="post-profile-image">
										<img src="./images/user_<?php echo $_SESSION['user_id'] ?>.jpg" alt="Profile Picture">
									</div>
									<div class="post-profile-name">
										<span><?php echo ucfirst($userFull['name']) ?> </span>
										<span><?php echo date('F j \\a\\t g:i A'); ?> <span aria-hidden="true"> · </span> <img src="./images/public.svg" width="12" height="12" alt="Shared with Public"></span>
									</div>
								</div>
								<div class="right-post-card-header">
									<img src="./images/post-menu.svg" width="20" height="20" alt="Post menu">
								</div>
			
							</div>
							<div class="post-content">
								<div> ${posttext}  </div>
							</div>
							<div class="post-likes-comments-share">
								<div class="left-post-likes">
									<div class="img-reactes">
										<img height="18" role="presentation" width="18"
											src="./images/reaction-like.svg">
										<img height="18" role="presentation" width="18"
											src="./images/reaction-love.svg">
									</div> 195K
								</div>
								<div class="right-post-likes">
									<span>1.2M comments</span>
									<span>34K shares</span>
									<span>9.6M views</span>
								</div>
							</div>
							<div class="post-like-comments-share-btn">
								<div class="like-btn">
									<i data-visualcompletion="css-img"></i>
									<span>Like</span>
								</div>
								<div class="comment-btn">
									<i data-visualcompletion="css-img"></i>
									<span>Comment</span>
								</div>
								<div class="share-btn1">
									<i data-visualcompletion="css-img"></i>
									<span>Share</span>
								</div>
							</div>
						</div>`).show('slow');

							// 2. Clear input and disable button
							$('.post-text-input').val('');
							$('#post-form').prop('disabled', true);
						}
					});
				}
				return false;
			});

			// tab active class bace on that clik change section in .post-section-boady
			$('.left-profile-bottom-tab span').click(function () {
				$('.left-profile-bottom-tab span').removeClass('active-tab');
				$(this).addClass('active-tab');

				var tabId = $(this).attr('id');

				if (tabId === 'all-tab') {
					$('.post-section').show();
					$('.about-section').hide();
					$('.friends-section').hide();
				} else if (tabId === 'friends-tab') {
					$('.post-section').hide();
					$('.friends-section').show();
					$('.about-section').hide();
				} else if (tabId === 'about-tab') {
					$('.post-section').hide();
					$('.friends-section').hide();
					$('.about-section').show();
				}
			});
			// about section left menu click function
			$('.left-about-content ul li').click(function () {
				$('.left-about-content ul li').removeClass('active');
				$(this).addClass('active');
				var sectionText = $(this).text().toLowerCase().replace(/\s+/g, '-');
				$('.right-about-content > div').hide();
				$('.' + sectionText + '-section').show();
			});

			// friends search bar function
			$('#friends-search').on('input', function () {
				var value = $(this).val().toLowerCase();
				$('#my-friends-list .friend-card').each(function () {
					var friendName = $(this).find('.friend-profile-name span').text().toLowerCase();
					if (friendName.includes(value)) {
						$(this).show();
					} else {
						$(this).hide();
					}
				});
			});

		});
	</script>
</body>

</html>