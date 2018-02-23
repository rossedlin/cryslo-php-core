<?php
namespace Cryslo\Api;

use Cryslo\Api;
use Cryslo\Core;
use Cryslo\Object;

/**
 * Created by PhpStorm.
 *
 * @author  Ross Edlin <contact@rossedlin.com>
 *
 * Date: 16/09/2017
 * Time: 21:34
 *
 * Class WordPress
 *
 * @package Cryslo\Feed
 */
class WordPress
{
	/**
	 * @param string $url
	 *
	 * @return Object\WordPress\Post
	 * @throws \Exception
	 */
	public static function getPost($url)
	{
		$json = Api::query((string)$url);

		//todo - json schema validation

		/** @var \stdClass $obj */
		$array = json_decode($json);

		foreach ($array as $obj)
		{
			return self::_buildPost($obj);
		}

		return new Object\WordPress\Post();
	}

	/**
	 * @param string $url
	 *
	 * @return Object\WordPress\Post[]
	 * @throws \Exception
	 */
	public static function getPosts($url)
	{
		$json = Api::query((string)$url);

		//todo - json schema validation

		/** @var \stdClass $obj */
		$array = json_decode($json);

		$posts = [];
		foreach ($array as $obj)
		{
			$posts[] = self::_buildPost($obj);
		}

		return $posts;
	}

	/**
	 * @param $url
	 *
	 * @return array
	 * @throws \Exception
	 */
	public static function getTag($url)
	{
		$json = Api::query((string)$url);

		//todo - json schema validation

		/** @var \stdClass $obj */
		$array = json_decode($json);

		die_r($array);
	}

	/**
	 * @param $url
	 *
	 * @return Object\WordPress\Tag[]
	 * @throws \Exception
	 */
	public static function getTags($url)
	{
		$json = Api::query((string)$url);

		//todo - json schema validation

		/** @var \stdClass $obj */
		$array = json_decode($json);

		$tags = [];
		foreach ($array as $obj)
		{
			$tag                 = self::_buildTag($obj);
			$tags[$tag->getId()] = $tag;
		}

		return $tags;
	}

	/**
	 * @param $url
	 *
	 * @return array
	 * @throws \Exception
	 */
	public static function getUser($url)
	{
		$json = Api::query((string)$url);

		//todo - json schema validation

		/** @var \stdClass $obj */
		$obj = json_decode($json);

		return self::_buildUser($obj);
	}

	/**
	 * @param \stdClass $obj
	 *
	 * @return Object\WordPress\Post
	 */
	private static function _buildPost(\stdClass $obj)
	{
		$post = new Object\WordPress\Post();

		/**
		 *
		 */
		$post->setId($obj->id);
		$post->setAuthorId($obj->author);
		$post->setSlug($obj->slug);
		$post->setDate($obj->date);
		$post->setStatus($obj->status);
		$post->setTitle($obj->title->rendered);
		$post->setContent($obj->content->rendered);
		$post->setExcerpt($obj->excerpt->rendered);

		/**
		 * Tags
		 */
		foreach ($obj->tags as $key => $id)
		{
			$tag = new Object\WordPress\Tag();
			$tag->setId($id);
			$tag->setName($obj->tag_names[$key]);
			$tag->setSlug($obj->tag_slugs[$key]);

			$post->addTag($tag);
		}

		/**
		 * Embedded Items
		 */
		if (isset($obj->_embedded))
		{
			$_embedded = (array)$obj->_embedded;

			if (isset($_embedded['author']) && is_array($_embedded['author']))
			{
				foreach ($_embedded['author'] as $author)
				{
					/**
					 * Author / User
					 */
					$post->setUser(self::_buildUser($author));

					break;
				}

			}

			if (isset($_embedded['wp:featuredmedia']) && is_array($_embedded['wp:featuredmedia']))
			{
				foreach ($_embedded['wp:featuredmedia'] as $img)
				{
					/**
					 * Author / User
					 */
					$post->setFeaturedMedia(self::_buildImage($img));

					break;
				}
			}
		}

		return $post;
	}

	/**
	 * @param \stdClass $obj
	 *
	 * @return Object\WordPress\Tag
	 */
	public static function _buildTag(\stdClass $obj)
	{
		$tag = new Object\WordPress\Tag();

		/**
		 *
		 */
		$tag->setId($obj->id);
		$tag->setSlug($obj->slug);
		$tag->setName($obj->name);

		return $tag;
	}

	/**
	 * @param \stdClass $obj
	 *
	 * @return Object\WordPress\User
	 */
	public static function _buildUser(\stdClass $obj)
	{
		$user = new Object\WordPress\User();

		/**
		 *
		 */
		$user->setId($obj->id);
		$user->setEmail($obj->contact->email);
		$user->setDisplayName($obj->name);
		$user->setDescription($obj->description);

		foreach ($obj->avatar_urls as $key => $url)
		{
			$user->addAvatar($key, $url);
		}

		$user->setGooglePlus($obj->contact->googleplus);
		$user->setFacebook($obj->contact->facebook);
		$user->setTwitter($obj->contact->twitter);
		$user->setLinkedin($obj->contact->linkedin);
		$user->setInstagram($obj->contact->instagram);
		$user->setGithub($obj->contact->github);

		return $user;
	}

	/**
	 * @param \stdClass $obj
	 *
	 * @return array|null
	 */
	public static function _buildImage(\stdClass $obj)
	{
		if (isset($obj->media_type) && $obj->media_type == 'image' && isset($obj->media_details->sizes))
		{
			/**
			 * @var \stdClass $sizes
			 */
			$sizes = (array)$obj->media_details->sizes;

			$featuredMedia = [];

			/**
			 *
			 */
			if (isset($sizes['blog-100x100']->source_url))
				$featuredMedia[Object\WordPress\Post::SIZE_BLOG_100x100] = $sizes['blog-100x100']->source_url;

			/**
			 * Thumbnail
			 */
			if (isset($sizes['thumbnail']->source_url))
				$featuredMedia[Object\WordPress\Post::SIZE_THUMBNAIL] = $sizes['thumbnail']->source_url;

			/**
			 * Original
			 */
			if (isset($sizes['full']->source_url))
				$featuredMedia[Object\WordPress\Post::SIZE_ORIGINAL] = $sizes['full']->source_url;

			return $featuredMedia;
		}

		return null;
	}
}