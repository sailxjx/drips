class Hanoi
  def initialize(num)
    @num = num
    @hanDict = [
      (1..@num).to_a,
      [],
      []
    ]
  end

  def move
    print @hanDict
  end
end