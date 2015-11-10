include /etc/nginx/upstream.d/<?php echo $entry['normalized'] ?>;

server {
  listen       <?php echo $entry['listen'] ?>;
  server_name  <?php echo $entry['name'] ?>;

  location / {
    proxy_pass          http://<?php echo $entry['normalized'] ?>;
    proxy_next_upstream error timeout invalid_header http_500 http_502 http_503 http_504;
    proxy_redirect      off;
    proxy_buffering     off;
    proxy_set_header    Host            $host;
    proxy_set_header    X-Real-IP       $remote_addr;
    proxy_set_header    X-Forwarded-For $proxy_add_x_forwarded_for;
 }
}