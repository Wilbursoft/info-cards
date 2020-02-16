#!/bin/sh -l

echo "param recieved: $1"
time=$(date)
echo ::set-output name=time::$time
