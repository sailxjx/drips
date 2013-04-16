class AcvMulti

  def initialize(totalNum)
    @totalNum = totalNum
    @stepNum = 0
    @stepList = []  # each steps by the quickest route
    @cmdList = [:x, :ctrl, :a, :c, :v]
    @primes = {}
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

  # f(n) = min(f(n-1) + 1, f(n/k) + k + 5)
  def get_steps(n, num = 0, list = [])
    if n == 1  # f(1) = 1
      return 1 + num, [:x].concat(list)
    end
    if @primes[n]
      return @primes[n]['num'] + num, @primes[n]['list'].clone.concat(list)
    end
    allRoutes = [get_steps(n - 1, num + 1, [:x].concat(list))]
    get_divisor(n).each do |k|
      allRoutes.push get_steps(n / k, num + k + 5, [:ctrl, :a, :ctrl, :c, :ctrl].concat([:v] * k).concat(list))
    end
    return allRoutes.sort_by {|x,y| x} [0]
  end

end

n = 15
acvRun = AcvMulti.new(n).run
puts "steps: #{acvRun.get_step_num}"
puts "route: #{acvRun.get_step_list}"
