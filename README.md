## looking glass api

This is the server counterpart to my [lg-web](http://github.com/Wintereise/lg-web) frontend. It performs server-side commands, and returns the responses back to the authenticated caller as JSON payloads.

#### Depends on

1. php5-sqlite
2. **php5-phalcon**

#### Methods available

*via HTTP GET* -> /api/v1/{task}/{target}[/]?{mask}

*mask* is optional, and only useful for OpenBGPd BGP table lookups. *task* may be any of the following,

1. ping
2. ping6
3. traceroute
4. traceroute6
5. bgp
6. stream

The traceroute commands return a live stream ID which can then be used to stream their output in real time in conjunction with task 6. This makes it seem as if the user is running the command on their own system.

The default API key (HTTP basic Auth - **username only**) is `odske710r3KyS8e32X5zCKnIjV82L6S4odske710r3KyS8e32X5zCUnIjV82L6S4`

*via HTTP PUT* -> /api/v1/update-key/{key}

Here, *key* is the new API key for the instance. It's recommended to update the key immediately after install. Note that it has to be exactly **64 characters long**.

This is doable like [this](https://gist.github.com/Wintereise/cdc9e2d7e12f7809ad19), or via the API -- whichever you prefer.

A typical response looks like this,

`{"state":"ok","code":200,"timestamp":1422285039,"message":"The stream object has been successfully created.","data":"54c658ef5ed8d"}`

Tools it's meant to be used with,

1. inet-utils ping/ping6
2. inet-utils traceroute/traceroute6
3. openbgpd bgpctl