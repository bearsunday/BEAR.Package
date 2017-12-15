# BEAR.Sunday HelloWorld benchmark

## Installation

    composer install --no-dev
    composer dump-autoload --no-dev

## Usage

### Run server

    php -S 127.0.0.1:8080/ -t bootstrap/bench.php

### Run test

    ab -t 10 -c 10 http://127.0.0.1:8080/; curl http://127.0.0.1:8080/?result

```
$ ab -t 10 -c 10 http://127.0.0.1:8080/; curl http://127.0.0.1:8080/?result

This is ApacheBench, Version 2.3 <$Revision: 1796539 $>
Copyright 1996 Adam Twiss, Zeus Technology Ltd, http://www.zeustech.net/
Licensed to The Apache Software Foundation, http://www.apache.org/

Benchmarking 127.0.0.1 (be patient)
Finished 749 requests


Server Software:        
Server Hostname:        127.0.0.1
Server Port:            8080

Document Path:          /
Document Length:        40 bytes

Concurrency Level:      10
Time taken for tests:   10.013 seconds
Complete requests:      749
Failed requests:        0
Total transferred:      146804 bytes
HTML transferred:       29960 bytes
Requests per second:    74.80 [#/sec] (mean)
Time per request:       133.683 [ms] (mean)
Time per request:       13.368 [ms] (mean, across all concurrent requests)
Transfer rate:          14.32 [Kbytes/sec] received

Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:        0    0   0.1      0       3
Processing:    15  133  12.2    132     160
Waiting:       14  132  12.2    132     160
Total:         15  133  12.2    133     160

Percentage of the requests served within a certain time (ms)
  50%    133
  66%    137
  75%    140
  80%    142
  90%    146
  95%    151
  98%    156
  99%    157
 100%    160 (longest request)
{
    "greeting": "Hello BEAR.Sunday"
}
| load | 1.479 | 1.479 | 11.9% |
| app | 10.531 | 9.051 | 72.6% |
| route | 10.626 | 0.095 | 0.8% |
| request | 12.451 | 1.825 | 14.6% |
| transfer | 12.474 | 0.023 | 0.2% |
```