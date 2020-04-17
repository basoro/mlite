<?php
/* * * * * * * * * * * * * * *
* Returns all published posts
* * * * * * * * * * * * * * */
function getPublishedPosts() {
	// use global $conn object in function
	global $connection;
	$page = (isset($_GET['page'])) ? $_GET['page'] : 1;
	$perPage = 10;
	$startPoint = ($page - 1) * $perPage;
	$sql = "SELECT * FROM website_posts WHERE post_type = 'post' AND post_status = 'publish' ORDER BY post_date DESC LIMIT $startPoint,$perPage";
	$result = mysqli_query($connection, $sql);

	// fetch all posts as an associative array called $posts
	$posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

	return $posts;
}

/* * * * * * * * * * * * * * *
* Returns all published pages
* * * * * * * * * * * * * * */
function getPublishedPages() {
	// use global $conn object in function
	global $connection;
	$page = (isset($_GET['page'])) ? $_GET['page'] : 1;
	$perPage = 10;
	$startPoint = ($page - 1) * $perPage;
	$sql = "SELECT * FROM website_posts WHERE post_type = 'page' AND post_status = 'publish' ORDER BY post_date DESC LIMIT $startPoint,$perPage";
	$result = mysqli_query($connection, $sql);

	// fetch all posts as an associative array called $posts
	$posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

	return $posts;
}


/* * * * * * * * * * * * * * *
* Returns a single post
* * * * * * * * * * * * * * */
function getPost($post_id){
	global $connection;
	// Get single post slug
	$post_id = isset($_GET['post_id'])?$_GET['post_id']:null;
	$sql = "SELECT * FROM website_posts WHERE post_id = '$post_id' AND post_type = 'post' AND post_status = 'publish'";
	$result = mysqli_query($connection, $sql);

	// fetch query results as associative array.
	$post = mysqli_fetch_assoc($result);
	return $post;
}

/* * * * * * * * * * * * * * *
* Returns a single page
* * * * * * * * * * * * * * */
function getPage($page_id){
	global $connection;
	// Get single post slug
	$page_id = $_GET['page_id'];
	$sql = "SELECT * FROM website_posts WHERE post_id = '$page_id' AND post_type = 'page' AND post_status = 'publish'";
	$result = mysqli_query($connection, $sql);

	// fetch query results as associative array.
	$page = mysqli_fetch_assoc($result);
	return $page;
}
// more functions to come here ...
?>
