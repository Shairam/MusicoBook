<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Post extends CI_Model
{
    public function __construct()
    {

        // placing it here fails: $this has no `load` property yet.
        // $this->load->database(); <!-- NO WAY JOSÉ!
        parent::__construct();
        // placing it here should work as the parent class has added that property
        // during it's own constructor
        $this->load->database();
    }

    public function userPosts($username)           // Fetch all posts made by a particular user 
    {

        $this->db->order_by("createdOn", "desc");
        $query = $this->db->get_where("Posts", array('createdBy' => $username));
        $resultArr =  $query->result_array();
        if ($resultArr) {
            foreach ($resultArr as &$postItem) {
                $this->testImagesView($postItem);
            }
        }
        return $resultArr;
    }

    public function createNewPost($title, $description)  // Creates a new post 
    {
        $date = date('Y-m-d H:i:s');
        $data = array(
            'createdBy' => $this->session->userdata("userdata")["username"],
            'createdOn' => $date,
            'description' => $description,
            'title' => $title
        );
        $this->db->insert('Posts', $data);
    }

    public function loadPostImages($post_id)       // Fetch images' URLs of a post if available
    {
        $query = $this->db->get_where("Post_Images", array('postId' => $post_id));
        return $query->result_array();
    }

    public function loadHomePosts()         // Fetches the list of posts made by currently logged in user and also the posts made by that user's followers
    {

        $result = $this->db->query("(SELECT Posts.*, Users.imageURL FROM Posts INNER JOIN
    Follows on Posts.createdBy = Follows.followed_user INNER JOIN Users on Follows.followed_user = Users.username WHERE Follows.followed_by =\"" . $this->session->userdata("userdata")["username"] . "\")
     UNION 
     (SELECT Posts.*,  Users.imageURL FROM Posts INNER JOIN Users on Posts.createdBy = Users.username WHERE Users.username = \"" . $this->session->userdata("userdata")["username"] . "\") ORDER BY `createdOn` DESC
        ");
        $resultArr = $result->result_array();
        if ($resultArr) {
            foreach ($resultArr as &$postItem) {
                $this->testImagesView($postItem);
            }
        }
        return $resultArr;
    }

    public function findLinks($postDescription)
    {
        preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $postDescription, $match);
        return $match[0];
    }

    public function testImagesView(&$postItem)  // Perform image URL check
    {
        $postItem["ImageLists"] = array();
        $linksArr = $this->findLinks($postItem["description"]);

        if (isset($linksArr))
            foreach ($linksArr as $imageLink) {
                if ($this->checkImagesURL($imageLink)) {
                    array_push($postItem["ImageLists"], $imageLink);
                }
            }
    }

   public function checkImagesURL($url)
	{
		$imgExts = array("gif", "jpg", "jpeg", "png", "tiff", "tif");
		$urlExt = pathinfo($url, PATHINFO_EXTENSION);
		if (in_array($urlExt, $imgExts)) {
			return true;
		}
    }
    
    public function checkProfileImage($url){

            if ($url == "" || $url == null) {
                $url = "https://avatarsed1.serversdev.getgo.com/2205256774854474505_medium.jpg";   // update profile picture of a new user if URL not given
            } else {
                if (!$this->checkImagesURL($url)) {
                    return null;
                }
            }
            return $url;
        }

}
