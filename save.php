<?php
header('Content-Type: application/json');
$postsFile = 'posts.json';
if (!file_exists($postsFile)) {
  file_put_contents($postsFile, json_encode([]));
}
$posts = json_decode(file_get_contents($postsFile), true) ?? [];

$data = json_decode(file_get_contents('php://input'), true);

if ($data['type'] === 'post') {
  array_unshift($posts, [
    'id' => $data['id'],
    'name' => strip_tags($data['name']),
    'content' => strip_tags($data['content']),
    'image' => strip_tags($data['image'] ?? null),
    'avatar' => $data['avatar'],
    'comments' => [],
    'voteScore' => 0,
    'voters' => []
  ]);
  if (count($posts) > 100) array_pop($posts);
} elseif ($data['type'] === 'comment') {
  foreach ($posts as &$p) {
    if ($p['id'] == $data['postId']) {
      $p['comments'][] = [
        'name' => strip_tags($data['name']),
        'content' => strip_tags($data['content'])
      ];
      break;
    }
  }
} elseif ($data['type'] === 'vote') {
  $postId = $data['postId'];
  $username = strip_tags(trim($data['name'] ?? 'anonymous'));
  $voteValue = (int)$data['vote'];
  foreach ($posts as &$p) {
    if ($p['id'] == $postId) {
      if (!isset($p['voters']) || !is_array($p['voters'])) $p['voters'] = [];
      if (!isset($p['voteScore'])) $p['voteScore'] = 0;
      $oldVote = $p['voters'][$username] ?? 0;
      if ($oldVote === $voteValue) {
        unset($p['voters'][$username]);
      } else {
        $p['voters'][$username] = $voteValue;
      }
      $score = 0;
      foreach ($p['voters'] as $v) $score += $v;
      $p['voteScore'] = $score;
      break;
    }
  }
}
file_put_contents($postsFile, json_encode($posts));
echo json_encode($posts);
?>
