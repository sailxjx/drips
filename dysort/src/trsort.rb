# O(n^2)

module Trsort
  def stepIn(dataInput)
    dataLen = dataInput.length
    diff = nil
    dataResule = {}
    for i in 0...dataLen-1
      for n in i+1...dataLen
        diff2 = dataInput[n] - dataInput[i]
        if diff == nil or diff < diff2
          diff = diff2
          dataResule["#{i},#{n}"] = diff
        end
      end
    end
    rIdxs = dataResule.sort_by {|k,v| -v} [0][0].split ','
    return [dataInput[rIdxs[0].to_i], dataInput[rIdxs[1].to_i]]
  end
end