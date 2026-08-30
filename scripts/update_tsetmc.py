import json, urllib.request, datetime, time

HEADERS={"User-Agent":"Mozilla/5.0","Accept":"application/json"}
MARKET="https://cdn.tsetmc.com/api/ClosingPrice/GetMarketWatch?market=0&industrialGroup=&paperTypes%5B0%5D=1&paperTypes%5B1%5D=2&paperTypes%5B2%5D=3&paperTypes%5B3%5D=4&paperTypes%5B4%5D=5&paperTypes%5B5%5D=6&paperTypes%5B6%5D=7&paperTypes%5B7%5D=8&paperTypes%5B8%5D=9&showTraded=false&withBestLimits=false&hEven=0&RefID=0"

def get(url):
    req=urllib.request.Request(url,headers=HEADERS)
    with urllib.request.urlopen(req,timeout=30) as r:
        return json.loads(r.read().decode("utf-8"))

raw=get(MARKET)
rows=raw.get("marketwatch",[])
symbols=[]
for x in rows:
    code=str(x.get("insCode",""))
    sym=str(x.get("lVal18AFC","")).strip()
    if not code or not sym: continue
    symbols.append({
        "id":code,
        "symbol":sym,
        "company_name":str(x.get("lVal30","")).strip(),
        "market":str(x.get("flow","")),
        "last_price":x.get("pDrCotVal",x.get("pl","")),
        "closing_price":x.get("pClosing",x.get("pc","")),
        "change_percent":x.get("zTotTran",x.get("z","")),
        "holders":[]
    })

# Fetch holders only for first 500 liquid/returned instruments to keep GitHub Action reliable.
# The symbol list itself remains complete.
for i,x in enumerate(symbols[:500]):
    try:
        url="https://cdn.tsetmc.com/api/Shareholder/GetInstrumentShareHolderLast/"+x["id"]
        j=get(url)
        arr=j.get("shareHolder") or j.get("shareHolders") or j.get("data") or []
        if isinstance(arr,list):
            x["holders"]=[{
                "name":h.get("shareHolderName") or h.get("name") or "",
                "percent":h.get("perOfShares",h.get("percent",""))
            } for h in arr[:20]]
        time.sleep(.05)
    except Exception:
        pass

out={
 "updated_at":datetime.datetime.now(datetime.timezone.utc).isoformat(),
 "total":len(symbols),
 "symbols":symbols
}
with open("data/market.json","w",encoding="utf-8") as f:
    json.dump(out,f,ensure_ascii=False,separators=(",",":"))
print("saved",len(symbols))
