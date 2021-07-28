<?php

namespace api\modules\v1\controllers;
use Yii;
use api\modules\v1\resources\UserMenu;
use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;
use yii\rest\IndexAction;
use yii\rest\OptionsAction;
use yii\rest\Serializer;
use yii\rest\ViewAction;
use yii\web\HttpException;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\HttpHeaderAuth;
use yii\filters\auth\QueryParamAuth;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\models\User;


class MenuController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'api\modules\v1\resources\UserMenu';
    /**
     * @var array
     */
    public $serializer = [
        'class' => Serializer::class,
        'collectionEnvelope' => 'items'
    ];

    /**
     * @inheritdoc
     */

  public function behaviors()
    {
        $behaviors = parent::behaviors();





        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                HttpBasicAuth::class,
                HttpBearerAuth::class,
                HttpHeaderAuth::class,
                QueryParamAuth::class
            ],
            //'except' => ['options'],
            'only' => ['view', 'index', 'create', 'update', 'saveuserimage'],
        ];


       // $behaviors['authenticator']['tokenParam'] = 'access-token';
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'only' => ['view', 'index', 'create', 'update', 'saveuserimage'],
            'rules' => [
                [
                    'actions' => ['view', 'index', 'create', 'update', 'saveuserimage'],
                    'allow' => true,
                    'roles' => ['@'],
                ],
            ]
        ];
        /* $behaviors['authenticator']['except'] = ['options'];
     *  $behaviors['verbs'] = [
         'class' => VerbFilter::className(),
         'actions' => [
             'index' => ['GET', 'HEAD'], //instead of  'index' => ['GET', 'HEAD']

             'view' => ['GET', 'HEAD'],

             'create' => ['POST'],

             'update' => ['PUT', 'PATCH'],

             'delete' => ['DELETE']
         ]
     ];*/

        return $behaviors;
    }


    public function actions()
    {
        /* return [
             'index' => [
                 'class' => IndexAction::class,
                 'modelClass' => $this->modelClass,
                 'prepareDataProvider' => [$this, 'prepareDataProvider']
             ],
             'view' => [
                 'class' => ViewAction::class,
                 'modelClass' => $this->modelClass,
                 'findModel' => [$this, 'findModel']
             ],
             'options' => [
                 'class' => OptionsAction::class
             ]
         ];*/
    }

    private $_verbs = ['POST', 'PUT', 'GET', 'OPTIONS'];

    public function actionOptions ()
    {
        if (Yii::$app->getRequest()->getMethod() !== 'OPTIONS') {
            Yii::$app->getResponse()->setStatusCode(405);
        }
        $options = $this->_verbs;
        Yii::$app->getResponse()->getHeaders()->set('Allow', implode(', ', $options));
       // throw new HttpException(200);
    }

    public function actionIndex()
    {

      //  $menuQuery=UserMenu::find()->andWhere(['ip'=>1, 'user_id'=>\Yii::$app->user->identity->id, 'FROM_UNIXTIME(created_at, "%Y-%m-%d")' => date('Y-m-d',time())])->orderBy(['id'=>SORT_DESC]);
        $menuQuery=UserMenu::find()->andWhere(['ip'=>1, 'user_id'=>\Yii::$app->user->identity->id])->orderBy(['id'=>SORT_DESC]);

        $count=$menuQuery->count();
        $pages = new Pagination(['totalCount' => $count, 'pageSize' => 6]);
        $dishes=$menuQuery->offset($pages->offset)
            ->limit($pages->limit)->all();
        //LinkPager::getPageRange();
        // $pageCount=$pages->getPageCount();
        $pageInfo=['pageCount'=>$pages->getPageCount(), 'totalCount'=>$count];
        return ['dishes'=>$dishes, 'pages'=>$pageInfo];

        //$count=$menuQuery->count();
       // $pages = new Pagination(['totalCount' => $count, 'pageSize' => 6]);
      //  $dishes=$menuQuery->all();
        //LinkPager::getPageRange();
        // $pageCount=$pages->getPageCount();
       // $pageInfo=['pageCount'=>$pages->getPageCount(), 'totalCount'=>$count];
       // $user=\Yii::$app->user->identity;




       // return ['dishes'=>$dishes];
    }

  
    public function actionView($id)
    {
       
        $model = UserMenu::find()
            ->andWhere(['id' => (int)$id])
            ->one();
        if (!$model) {
            throw new HttpException(404);
        }
        return $model;
    }

    public function actionCreate()
    {



       
        $model = new \common\models\UserMenu;
       // $model->load(Yii::$app->request->BodyParams);
       $model->event=json_decode(Yii::$app->request->getRawBody())->event;
       $model->name=json_decode(Yii::$app->request->getRawBody())->name;
        $model->created_at=ceil((json_decode(Yii::$app->request->getRawBody())->created_at)/1000);
       // $model->full=json_decode(Yii::$app->request->getRawBody())->full;
       $model->hungry=json_decode(Yii::$app->request->getRawBody())->hungry;
        $model->emoticon=json_decode(Yii::$app->request->getRawBody())->emoticon;
       // $model->user_id=Yii::$app->user->identity->id;
        $model->ip=1;

       // $model->thumbnail_path=json_decode(Yii::$app->request->getRawBody())->thumbnail;
       // $file=json_decode(Yii::$app->request->getRawBody())->thumbnail;
        $thumbnail=$this->saveuserimage(json_decode(Yii::$app->request->getRawBody())->thumbnail);
        if ($thumbnail) {
            $model->thumbnail_path=$thumbnail;
            if ($model->save()) {
                return ['model'=>$model, 'result'=>true];
            }
            else {
                return ['errors'=>$model->errors, 'result'=>false, 'thumbnail'=>$thumbnail];
            }

        }
        else {
            return ['errors'=>'Не удалось закачать фото на сервер', 'result'=>false];
        }
       // $model->created_at=time();
      //  $model->save(false);

      //  $menuQuery=UserMenu::find()->andWhere(['ip'=>1, 'user_id'=>\Yii::$app->user->identity->id, 'FROM_UNIXTIME(created_at, "%Y-%m-%d")' => date('Y-m-d',time())])->orderBy(['id'=>SORT_DESC]);
        //$count=$menuQuery->count();
        // $pages = new Pagination(['totalCount' => $count, 'pageSize' => 6]);
      //  $dishes=$menuQuery->all();
        //LinkPager::getPageRange();
        // $pageCount=$pages->getPageCount();
        // $pageInfo=['pageCount'=>$pages->getPageCount(), 'totalCount'=>$count];
        // $user=\Yii::$app->user->identity;





    }

    public function actionUpdate($id)
    {



       $model = UserMenu::find()
            ->andWhere(['id' => (int)$id])
            ->andWhere(['user_id' => \Yii::$app->user->identity->id])
            ->one();
       // $model = new \common\models\UserMenu;
        // $model->load(Yii::$app->request->BodyParams);
       // return ['model'=>$model, 'result'=>true];
       if ($model) {
            $model->full=json_decode(Yii::$app->request->getRawBody())->full;
            if ($model->save()) {
                return ['model'=>$model, 'result'=>true];
            }
            else {
                return ['errors'=>$model->errors, 'result'=>false];
            }

        }
        else {
            return ['errors'=>'Это фото не найдено или принадлежит не вам', 'result'=>false];
        }
       // $exception = Yii::$app->getErrorHandler()->exception;
      //  return ['errors'=>$exception->getMessage(), 'result'=>false];
        // $model->created_at=time();
        //  $model->save(false);

        //  $menuQuery=UserMenu::find()->andWhere(['ip'=>1, 'user_id'=>\Yii::$app->user->identity->id, 'FROM_UNIXTIME(created_at, "%Y-%m-%d")' => date('Y-m-d',time())])->orderBy(['id'=>SORT_DESC]);
        //$count=$menuQuery->count();
        // $pages = new Pagination(['totalCount' => $count, 'pageSize' => 6]);
        //  $dishes=$menuQuery->all();
        //LinkPager::getPageRange();
        // $pageCount=$pages->getPageCount();
        // $pageInfo=['pageCount'=>$pages->getPageCount(), 'totalCount'=>$count];
        // $user=\Yii::$app->user->identity;





    }

    public function actionSaveuserimage(){


        $data=['result'=>false];
        $image_name=time().'.jpg';
        $target_path = '/storage/users/';
        $user_id=\Yii::$app->user->identity->id;
        $file=json_decode(Yii::$app->request->getRawBody());
        // $data['img']=$file;
        if ($file->file) {
            $imagedata = $file->file;
            // $data['image0']=$imagedata;
            $imagedata = str_replace('data:image/jpeg;base64,', '',$imagedata);
            $imagedata = str_replace('data:image/jpg;base64,', '',$imagedata);
            $imagedata=str_replace(' ', '+', $imagedata);
            //  $data['image1']=$imagedata;
            $imagedata=base64_decode($imagedata);
            // $data['image2']=$imagedata;
            if (!file_exists(\Yii::$app->basePath.$target_path.$user_id)) {
                mkdir(\Yii::$app->basePath.$target_path.$user_id, 0777, true);
            }
            if (file_put_contents(\Yii::$app->basePath.$target_path.$user_id.'/'.$image_name, $imagedata)) {
                $data['result']=true;
                $data['image_url']=$user_id.'/'.$image_name;
                $data['url']='https://api.metabody.ru/api'.$target_path.$user_id.'/'.$image_name;
               // return $user_id.'/'.$image_name;

            }
            else {
               // return false;
            }

            //  if (file_put_contents($target_path, $imagedata)) {

            // }
            // return 'uploaded';


        }
        else {
           // return false;
        }

       return ['data'=>$data];
        // return['file'=>json_decode(Yii::$app->getRequest()->getRawBody(), true)];

    }

    protected static function saveuserimage($file){


        $data=['result'=>false];
        $image_name=time().'.jpg';
        $target_path = '/storage/users/';
        $user_id=\Yii::$app->user->identity->id;
       // $file=json_decode(Yii::$app->request->getRawBody());
        // $data['img']=$file;
        if ($file) {
            $imagedata = $file;
            // $data['image0']=$imagedata;
            $imagedata = str_replace('data:image/jpeg;base64,', '',$imagedata);
            $imagedata = str_replace('data:image/jpg;base64,', '',$imagedata);
            $imagedata=str_replace(' ', '+', $imagedata);
            //  $data['image1']=$imagedata;
            $imagedata=base64_decode($imagedata);
            // $data['image2']=$imagedata;
            if (!file_exists(\Yii::$app->basePath.$target_path.$user_id)) {
                mkdir(\Yii::$app->basePath.$target_path.$user_id, 0777, true);
            }
            if (file_put_contents(\Yii::$app->basePath.$target_path.$user_id.'/'.$image_name, $imagedata)) {
                $data['result']=true;
                $data['image_url']=$user_id.'/'.$image_name;
                $data['url']='https://api.metabody.ru/api'.$target_path.$user_id.'/'.$image_name;
                return $user_id.'/'.$image_name;
            }
            else {
                $data['errors']='Не удалось загрузить фото на сервер. Попробуйте еще раз!';
                return false;
            }

            //  if (file_put_contents($target_path, $imagedata)) {

            // }
            // return 'uploaded';


        }
        else {
            return false;
        }

      //  return ['data'=>$data];
        // return['file'=>json_decode(Yii::$app->getRequest()->getRawBody(), true)];

    }

}
