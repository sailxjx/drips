module Dysort
  def stepIn(dataList)
    minIdx = 0  # minimal index
    maxIdx = 0  # maximal index
    differ = 0  # max differ
    minIdxTmp = nil  # temp minimal index
    for i in 1...dataList.length
      if dataList[i] > dataList[maxIdx]  # replace the maximal index
        maxIdx = i  # new maximal index
        differ = dataList[i] - dataList[minIdx]  # new differ
      elsif dataList[i] < dataList[minIdx] and ( minIdxTmp == nil or dataList[i] < dataList[minIdxTmp] )  # replace the temp minimal index
        minIdxTmp = i  # change temp minimal index
      elsif minIdxTmp != nil and dataList[i] - dataList[minIdxTmp] > differ  # if current index minus temp minimal index is bigger than differ, replace it
        differ = dataList[i] - dataList[minIdxTmp]  # new differ
        minIdx = minIdxTmp  # new minimal index
        maxIdx = i  # new maximal index
      else
        next
      end
    end
    return [dataList[minIdx], dataList[maxIdx]]
  end
end
