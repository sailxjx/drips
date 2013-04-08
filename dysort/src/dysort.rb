# O(n)

module Dysort
  def stepIn(list)
    min = 0  # minimal index
    max = 0  # maximal index
    differ = 0  # max differ
    minTmp = nil  # temp minimal index
    for i in 1...list.length
      if minTmp != nil and list[i] - list[minTmp] > differ  # if current index minus temp minimal index is bigger than differ, replace it
        differ = list[i] - list[minTmp]  # new differ
        min = minTmp  # new minimal index
        max = i  # new maximal index
      elsif list[i] > list[max]  # replace the maximal index
        max = i  # new maximal index
        differ = list[i] - list[min]  # new differ
      elsif list[i] < list[min] and ( minTmp == nil or list[i] < list[minTmp] )  # replace the temp minimal index
        minTmp = i  # change temp minimal index
      else
        next
      end
    end
    return [list[min], list[max]]
  end
end
