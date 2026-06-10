<?php

declare(strict_types=1);

namespace Drupal\api_demo\Controller;

use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

final class ApiDemoController extends ControllerBase {

  public function __construct(
    private readonly ClientInterface $httpClient,
  ) {}

  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('http_client'),
    );
  }

  public function post(int $id): JsonResponse {
    $url = "https://jsonplaceholder.typicode.com/posts/$id";

    try {
      $response = $this->httpClient->request('GET', $url, [
        'timeout' => 5,
        'headers' => [
          'Accept' => 'application/json',
        ],
      ]);

      $data = json_decode((string) $response->getBody(), TRUE);

      if (!is_array($data) || empty($data)) {
        return new JsonResponse([
          'error' => 'Post not found',
          'id' => $id,
        ], 404);
      }

      return new JsonResponse([
        'source' => 'JSONPlaceholder',
        'endpoint' => $url,
        'data' => $data,
      ]);
    }
    catch (GuzzleException $e) {
      return new JsonResponse([
        'error' => 'Remote API request failed',
        'message' => $e->getMessage(),
      ], 502);
    }
  }

}
