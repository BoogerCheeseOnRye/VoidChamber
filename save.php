<?php
header('Content-Type: application/json');
$postsFile = 'posts.json';
if (!file_exists($postsFile)) {
  file_put_contents($postsFile, json_encode([]));
}
$posts = json_decode(file_get_contents($postsFile), true);

$data = json_decode(file_get_contents('php://input'), true);

if ($data['type'] === 'post') {
  array_unshift($posts, [
    'id' => $data['id'],
    'name' => strip_tags($data['name']),
    'content' => strip_tags($data['content']),
    'image' => strip_tags($data['image']),
    'avatar' => $data['avatar'],
    'comments' => []
  ]);
  if (count($posts) > 100) array_pop($posts);
} elseif ($data['type'] === 'comment') {
  foreach ($posts as &$p) {
    if ($p['id'] == $data['postId']) {
      $p['comments'][] = [
        'name' => strip_tags($data['name']),
        'text' => strip_tags($data['text'])
      ];
      break;
    }
  }
}

file_put_contents($postsFile, json_encode($posts));
echo json_encode($posts);
?>
