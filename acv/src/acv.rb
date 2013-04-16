class Acv

  def initialize(totalNum)
    @totalNum = totalNum
    @stepNum = 0
    @stepList = []  # each steps by the quickest route
    @cmdList = [:a, :ca, :cc, :cv]
    @primes = {2=>{"num"=>2, "list"=>[:a, :a]}, 3=>{"num"=>3, "list"=>[:a, :a, :a]}, 5=>{"num"=>5, "list"=>[:a, :a, :a, :a, :a]}, 7=>{"num"=>7, "list"=>[:a, :a, :a, :a, :a, :a, :a]}, 11=>{"num"=>10, "list"=>[:a, :a, :a, :ca, :cc, :cv, :cv, :cv, :a, :a]}, 13=>{"num"=>10, "list"=>[:a, :a, :a, :a, :ca, :cc, :cv, :cv, :cv, :a]}, 17=>{"num"=>11, "list"=>[:a, :a, :a, :a, :ca, :cc, :cv, :cv, :cv, :cv, :a]}, 19=>{"num"=>12, "list"=>[:a, :a, :a, :a, :a, :a, :ca, :cc, :cv, :cv, :cv, :a]}, 23=>{"num"=>14, "list"=>[:a, :a, :a, :a, :a, :ca, :cc, :cv, :cv, :cv, :cv, :a, :a, :a]}, 29=>{"num"=>14, "list"=>[:a, :a, :a, :a, :a, :a, :a, :ca, :cc, :cv, :cv, :cv, :cv, :a]}, 31=>{"num"=>14, "list"=>[:a, :a, :a, :a, :a, :a, :ca, :cc, :cv, :cv, :cv, :cv, :cv, :a]}, 37=>{"num"=>15, "list"=>[:a, :a, :a, :a, :a, :a, :ca, :cc, :cv, :cv, :cv, :cv, :cv, :cv, :a]}, 41=>{"num"=>16, "list"=>[:a, :a, :a, :a, :a, :a, :a, :a, :ca, :cc, :cv, :cv, :cv, :cv, :cv, :a]}, 43=>{"num"=>16, "list"=>[:a, :a, :a, :a, :a, :a, :a, :ca, :cc, :cv, :cv, :cv, :cv, :cv, :cv, :a]}, 47=>{"num"=>17, "list"=>[:a, :a, :a, :ca, :cc, :cv, :cv, :cv, :ca, :cc, :cv, :cv, :cv, :cv, :cv, :a, :a]}, 53=>{"num"=>17, "list"=>[:a, :a, :a, :a, :ca, :cc, :cv, :cv, :cv, :a, :ca, :cc, :cv, :cv, :cv, :cv, :a]}, 59=>{"num"=>19, "list"=>[:a, :a, :a, :a, :a, :a, :ca, :cc, :cv, :cv, :cv, :a, :ca, :cc, :cv, :cv, :cv, :a, :a]}, 61=>{"num"=>17, "list"=>[:a, :a, :a, :a, :a, :ca, :cc, :cv, :cv, :cv, :ca, :cc, :cv, :cv, :cv, :cv, :a]}, 67=>{"num"=>19, "list"=>[:a, :a, :a, :a, :ca, :cc, :cv, :cv, :cv, :cv, :ca, :cc, :cv, :cv, :cv, :cv, :a, :a, :a]}, 71=>{"num"=>19, "list"=>[:a, :a, :a, :ca, :cc, :cv, :cv, :cv, :a, :ca, :cc, :cv, :cv, :cv, :cv, :cv, :cv, :cv, :a]}, 73=>{"num"=>18, "list"=>[:a, :a, :a, :a, :a, :a, :ca, :cc, :cv, :cv, :cv, :ca, :cc, :cv, :cv, :cv, :cv, :a]}, 79=>{"num"=>19, "list"=>[:a, :a, :a, :a, :ca, :cc, :cv, :cv, :cv, :a, :ca, :cc, :cv, :cv, :cv, :cv, :cv, :cv, :a]}, 83=>{"num"=>20, "list"=>[:a, :a, :a, :a, :a, :ca, :cc, :cv, :cv, :cv, :cv, :ca, :cc, :cv, :cv, :cv, :cv, :a, :a, :a]}, 89=>{"num"=>20, "list"=>[:a, :a, :a, :a, :a, :ca, :cc, :cv, :cv, :cv, :cv, :a, :a, :ca, :cc, :cv, :cv, :cv, :cv, :a]}, 97=>{"num"=>19, "list"=>[:a, :a, :a, :a, :a, :a, :ca, :cc, :cv, :cv, :cv, :cv, :ca, :cc, :cv, :cv, :cv, :cv, :a]}}
  end

  def run
    @stepNum, @stepList = get_steps(@totalNum)
    self
  end

  def get_step_num
    return @stepNum
  end

  def get_step_list
    return @stepList
  end

  # don't use this function in runtime!!
  def calculate_primes(range)
    primes = range.select do |x|
      r = true
      if x < 2
        r = false
      else
        for i in 2..Math.sqrt(x).to_i
          if x % i == 0
            r = false
            break
          end
        end
      end
      r
    end
    primeStepNumList = {}
    primes.each do |x|
      acv = Acv.new(x).run
      primeStepNumList[x] = {}
      primeStepNumList[x]['num'] = acv.get_step_num
      primeStepNumList[x]['list'] = acv.get_step_list
    end
    return primeStepNumList
  end

  protected
  def get_divisor(n)
    (2..Math.sqrt(n).to_i).select {|x| n % x == 0}
  end

  # f(n) = min(f(n-1) + 1, f(n/k) + k + 2)
  def get_steps(n, num = 0, list = [])
    if n == 1  # f(1) = 1
      return 1 + num, [:a].concat(list)
    end
    if @primes[n]
      return @primes[n]['num'] + num, @primes[n]['list'].clone.concat(list)
    end
    allRoutes = [get_steps(n - 1, num + 1, [:a].concat(list))]
    get_divisor(n).each do |k|
      allRoutes.push get_steps(n / k, num + k + 2, [:ca, :cc].concat([:cv] * k).concat(list))
    end
    return allRoutes.sort_by {|x,y| x} [0]
  end

end

## pre caculate the route of all prime numbers
# print Acv.new(10).calculate_primes(0..100)
# n = 300
n = 9
acvRun = Acv.new(n).run
puts "steps: #{acvRun.get_step_num}"
puts "route: #{acvRun.get_step_list}"
