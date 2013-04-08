baseDir = File.dirname(File.expand_path(__FILE__))

require "#{baseDir}/../src/trsort"

include Trsort

dataInput = [3,10,11,9,1,2,-1,12,7]
dataResule = [-1, 12]

resule = stepIn(dataInput)
print resule, "\n"
if resule == dataResule
  puts 'succ'
else
  puts 'fail'
end
