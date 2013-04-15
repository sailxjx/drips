baseDir = File.dirname(File.expand_path(__FILE__))
require "#{baseDir}/../src/acv.rb"

testCases = {
  2 => 2,
  10 => 9,
  99 => 20,
  100 => 18
}

testCases.map do |n, num|
  puts "test input: #{n}"
  acvRun = Acv.new(n).run
  puts "steps: #{acvRun.get_step_num}"
  puts "route: #{acvRun.get_step_list}"
  if acvRun.get_step_num != num
    puts "error"
    exit 1
  else
    puts "succ"
  end
end

exit 0