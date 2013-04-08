baseDir = File.dirname(File.expand_path(__FILE__))
require "#{baseDir}/../src/dysort"
require "#{baseDir}/../src/trsort"

dataInput = []
t = ARGV[0] ? ARGV[0].to_i : 1000
t.times { dataInput.push rand(999999) }

include Dysort
puts "start dysort"
start = Time.now
resule = stepIn(dataInput)
duration = Time.now - start
print resule, "\n"
puts "dysort: #{duration}"

include Trsort
puts "start trsort"
start = Time.now
resule = stepIn(dataInput)
duration = Time.now - start
print resule, "\n"
puts "trsort: #{duration}"