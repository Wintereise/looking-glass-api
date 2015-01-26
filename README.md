## looking glass api

This is the server counterpart to my [lg-web](http://github.com/Wintereise/lg-web) frontend. It performs server-side commands, and returns the responses back to the authenticated caller as JSON payloads.

#### Methods available
*via HTTP GET* -> /v1/api/{task}/{target}[/]?{mask}

*mask* is optional, and only useful for OpenBGPd BGP table lookups. *task* may be any of the following,

1. ping
2. ping6
3. traceroute
4. traceroute6
5. bgp

The default API key (HTTP basic Auth - **username only**) is `odske710r3KyS8e32X5zCKnIjV82L6S4odske710r3KyS8e32X5zCUnIjV82L6S4`

*via HTTP PUT* -> /v1/api/update-key/{key}

Here, *key* is the new API key for the instance. It's recommended to update the key immediately after install. Note that it has to be exactly **64 characters long**.

A typical response looks like this,

`{"state":"ok","code":200,"message":"The trace was successfully performed.","data":"traceroute to 8.8.8.8 (8.8.8.8), 30 hops max, 60 byte packets\n1  75-97-248-162-static.reverse.queryfoundry.net (162.248.97.75)  0.080 ms  0.015 ms  0.010 ms\n2  * * *\n3  any2ix.coresite.com (206.72.210.41)  0.606 ms  0.579 ms  0.614 ms\n4  209.85.241.83 (209.85.241.83)  0.720 ms 209.85.248.59 (209.85.248.59)  0.754 ms 209.85.250.97 (209.85.250.97)  0.729 ms\n5  google-public-dns-a.google.com (8.8.8.8)  0.380 ms  0.462 ms  0.456 ms"}`

Tools it's meant to be used with,

1. inet-utils ping/ping6
2. inet-utils traceroute/traceroute6
3. openbgpd bgpctl