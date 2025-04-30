function commaCheck(jparam){
  let checkid=jparam.split(' ');
  for (let i=0;i<checkid.length;i++){
    const str = document.getElementById(checkid[i]).value;
    if (str.match(/,/g)){
      alert(checkid[i]+":カンマは使えません");
      return false;
    }
  }
  //alert(checkid[i]+":入力OK");
  return true;
}

function deleteHost(host){
  if(window.confirm( host + " を削除してよろしいですか？")){
    return true;
  }else{
    window.alert(host + " の削除をキャンセルしました");
    return false;
  }
}
