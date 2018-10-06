# ci3-bootstrap-fileinput-sample
CodeIgniter における、kartik-v/bootstrap-fileinput の使用例です。

## 試し方
ウェブサーバーのドキュメントルートに移動してから次の操作をします。

```bash
git clone https://github.com/oki2a24/ci3-bootstrap-fileinput-sample.git
```

http://localhost/ci3-bootstrap-fileinput-sample/index.php/ajaxsubmission/index
Submit ボタンでテキストフォームの内容はサーバに送られますが、ファイルのアップロードはされません。

http://localhost/ci3-bootstrap-fileinput-sample/index.php/ajaxsynchronous/index
Submit ボタンで、ファイルアップロード -> テキストフォーム内容の送信、の順に実行しますが、アップロードしたファイルが上手く画面に表示されません。
