upstream <?php echo $entry['normalized'] ?>  {
<?php if(empty($upstreams)): ?>
  server 127.0.0.1 down;
<?php else: ?>
<?php if(count($upstreams) > 1): ?>
  ip_hash;
<?php endif ?>
<?php foreach($upstreams as $upstream): ?>
  server <?php echo $upstream['line'] ?>;
<?php endforeach ?>
<?php endif ?>
}
