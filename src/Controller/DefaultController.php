<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\BlogPost;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 */
class DefaultController extends AbstractController
{
    /**
     * @Route(
     *     "/",
     *     methods={"GET"},
     *     name="blog.index")
     */
    public function index(): JsonResponse
    {
        return $this->json(['action' => 'index', 'time' => time()]);
    }

    /**
     * @Route(
     *     "/list/{page}",
     *     methods={"GET"},
     *     name="posts.list",
     *     requirements={"page" = "\d+"},
     *     defaults={"page": 1})
     */
    public function list($page, Request $request): JsonResponse
    {
        $limit = (int)$request->get('limit', 5);
        $posts = $this->getDoctrine()->getRepository(BlogPost::class)->findAll();

        return $this->json([
            'page' => $page,
            'limit' => $limit,
            'data' => array_map(function(BlogPost $post) {
                return $this->generateUrl('post.get.by.slug', ['partName' => $post->getSlug()]);
            }, $posts)
        ]);
    }

    /**
     * @Route(
     *     "/post/{id}",
     *     methods={"GET"},
     *     requirements={"id" = "\d+"},
     *     name="post.get.by.id")
     * @ParamConverter(
     *     "post",
     *     class="App:BlogPost")
     */
    public function post($post): JsonResponse
    {
        return $this->json($post);
    }

    /**
     * @Route(
     *     "/post/{partName}",
     *     methods={"GET"},
     *     name="post.get.by.slug")
     * @ParamConverter(
     *     "post",
     *     class="App:BlogPost",
     *     options={"mapping": {"partName": "slug"}})
     */
    public function getPostBySlug($post): JsonResponse
    {
        // Same as findOneBy(['slug' => contain of {slug}])
        return $this->json($post);
    }

    /**
     * @Route(
     *     "/post",
     *     methods={"POST"},
     *     name="post.add")
     */
    public function addPost(Request $request): JsonResponse
    {
        $serializer = $this->get('serializer');
        /** @var BlogPost $blogPost */
        $blogPost = $serializer->deserialize($request->getContent(), BlogPost::class, 'json');
        $blogPost->setCreatedAt(new \DateTime('now'));
        $em = $this->getDoctrine()->getManager();
        $em->persist($blogPost);
        $em->flush();

        return $this->json($blogPost);
    }

    /**
     * @Route(
     *     "/post/{id}",
     *     methods={"DELETE"},
     *     name="blog.delete")
     */
    public function delete(BlogPost $post): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
