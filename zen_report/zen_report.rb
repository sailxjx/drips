# encoding: utf-8
require 'nokogiri'
require 'open-uri'
require 'uri'

sid = ARGV[0] ? ARGV[0] : nil
if sid == nil then exit 1 end

ZEN_INDEX = "http://192.168.0.246/index.php?m=my&f=index&sid=#{sid}"

uri = URI(ZEN_INDEX)

page = Nokogiri::HTML(open(ZEN_INDEX))
task_links = page.css('.linkbox2')[0].css('.nobr a')
task_links.each do |t|
  task_page = Nokogiri::HTML(open("#{uri.scheme}://#{uri.host}#{t[:href]}&sid=#{sid}"))
  puts task_page.css('.table-1 tr.nofixed a')
end