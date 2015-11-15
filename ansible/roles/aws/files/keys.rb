#!/usr/bin/env ruby
require "open-uri"
require "json"

url = "https://api.github.com/orgs/#{ARGV[1]}"
organization = JSON.parse(open(url).read)

members_url = organization["members_url"].gsub("{/member}", "")
members = JSON.parse(open(members_url).read)

keys = "#
# #{organization["name"]} keys
# #{members_url}
#
# --
"

for member in members.map{|member| member["login"].downcase}.sort
  member_keys = "https://github.com/#{member}.keys"

  info = "#
# @#{member}
# #{member_keys}
#
"

  keys += info + open(member_keys).read.gsub(/\r\n?/, "\n")
end

if keys.scan(/ssh-rsa/).count > 1
    File.open("/home/#{ARGV[0]}/.ssh/authorized_keys", "w") do |f|
        f.write(keys)
    end
else
    raise "keys look whack"
end
