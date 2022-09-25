FROM openresty/openresty:alpine-fat

LABEL maintainer="Einar Hansen <einar@einarhansen.dev>"

RUN mkdir /var/log/nginx

RUN apk add --no-cache openssl-dev git gcc
RUN opm install bungle/lua-resty-session zmartzone/lua-resty-openidc cdbattags/lua-resty-jwt
