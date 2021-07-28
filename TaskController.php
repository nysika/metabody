<?php

namespace frontend\controllers;
use common\models\User;
use common\models\Tasks;
use common\models\UserProgram;
use common\models\UserProfile;
use common\models\UserTexts;
use common\models\UserParams;
use common\models\UserTasks;
use common\models\UserRashod;
use common\models\Activity;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\UploadForm;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;

class TaskController extends \yii\web\Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'addgoal', 'addparams', 'addcalrashod', 'addusertask', 'addtexts', 'uploadphoto', 'activity', 'activitysearch', 'addactivity', 'experiment'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function(){
                            if (UserProfile::isUserProfileFilled() && UserProgram::isUserHasActiveProgram(Yii::$app->user->id))
                                return true;
                        }
                    ]


                ],
                'denyCallback' => function(){
                    if (!UserProfile::isUserProfileFilled()) {
                        return $this->redirect('/user/default/index');
                    }
                    else if (!UserProgram::isUserHasActiveProgram(Yii::$app->user->id)){
                        return $this->redirect('/user/default/program');
                    }


                }
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
// 'logout' => ['post']
                ]
            ]
        ];
    }


    public function actionIndex($day='today')
    {
        \Yii::$app->view->title='<h1>Список заданий</h1>';
        //\Yii::$app->view->params['title_show'] = 'no';
        //\Yii::$app->view->params['breadcrumbs'][] = 'Задачи';
        \Yii::$app->view->params['headerclass'] = ' user-cabinet';
        \Yii::$app->view->params['title2'] = '';
        $user_program=UserProgram::getProgramInfo();
        if ($day && $day=='tomorrow') {
            $day_week_today=date('N', strtotime(' +1 day'));
            $interval = date_diff(date_create(date('Y-m-d',$user_program->start_date)), date_create(date('Y-m-d', strtotime(' +1 day'))));
            $template='tomorrow';
        }
        else if ($day && $day=='yesterday') {
            $day_week_today=date('N', strtotime(' -1 day'));
            $interval = date_diff(date_create(date('Y-m-d',$user_program->start_date)), date_create(date('Y-m-d', strtotime(' -1 day'))));
            $template='yesterday';
        }
        else {
            $day_week_today=date('N');
            $interval = date_diff(date_create(date('Y-m-d',$user_program->start_date)), date_create(date('Y-m-d',time())));
            $template='index';
        }

        switch ($day_week_today) {
            case 1:
                $orwhere=array('=','monday',1);
                break;
            case 2:
                $orwhere=array('=','tuesday',1);
                break;
            case 3:
                $orwhere=array('=','wednesday',1);
                break;
            case 4:
                $orwhere=array('=','thursday',1);
                break;
            case 5:
                $orwhere=array('=','friday',1);
                break;
            case 6:
                $orwhere=array('=','saturday',1);
                break;
            case 7:
                $orwhere=array('=','sunday',1);
                break;
            default:
                $orwhere=array();
                break;
        }


        $number_day_program=$interval->format('%R%a')+1;
        $number_week_program=ceil($number_day_program/7);


        if ($number_day_program==1) {
            $orwhere2=array('=','firstday',1);
        }
        else if($number_day_program==Yii::$app->params['availablePrograms'][$user_program->program_id]['days']) {
            $orwhere2=array('=','lastday',1);
        }
        else {
            $orwhere2=array();
        }
        //$tasks=Tasks::find()->where(['period' => $number_day_program])->orwhere($orwhere)->all();
        //$tasks=Tasks::find()->where(['MOD("period", $number_day_program)' => 0])->orwhere($orwhere)->all();
        //$tasks=Tasks::find()->where(['=',$number_day_program.' % '. 'period',0])->orwhere($orwhere)->all();
       // $tasks=Tasks::find()->where($number_day_program.' % period = 0 and '.$number_day_program.'/ period <= repeatition')->orwhere($orwhere)->orwhere($orwhere2)->andwhere('day_from <= '.$number_day_program.' and day_to >= '.$number_day_program)->andwhere('program_id ='. $user_program->program_id. ' or program_id=0')->orderBy('sort DESC')->all();
        $tasks=Tasks::find()->where($number_day_program.' % period = ostatok')->orwhere($orwhere)->orwhere($orwhere2)->andwhere('day_from <= '.$number_day_program.' and day_to >= '.$number_day_program)->andwhere('program_id ='. $user_program->program_id. ' or program_id=0')->orderBy('sort DESC')->all();

        $required_tasks=Tasks::find()->where(['optional'=>1])->all();
        //$number_day_program=ceil((time()-$user_program->start_date)/(60*60*24));
        return $this->render($template,[
            'number_day' => $number_day_program,
            'number_week' => $number_week_program,
            'program'=>$user_program,
            'tasks'=>$tasks,
            'required_tasks'=>$required_tasks,
        ]);


    }


    public function actionView($id)
    {
       $this->layout='empty';
        $task=Tasks::find()->where(['id'=>$id])->one();
        $user_task = UserTasks::find()->where(['user_id' => Yii::$app->user->id, 'task_id' => $id])->andWhere('DATE(FROM_UNIXTIME(created_at)) = CURDATE()')->one();

       // $today=date('Y-m-d',time());


        return $this->render('taskinfo',[
            'task'=>$task,
            'user_task'=>$user_task,
        ]);

    }

    public function actionAddgoal() {
       if (Yii::$app->request->isAjax) {
          //  $program = UserProgram::find()->where(['id' => $_GET['user_program_id']])->one();
           $program=UserProgram::getProgramInfo();
            $program->goal_text = Yii::$app->request->get('goal_text');
            if ($program->save()) {
                return 1;
            } else {
                return 0;
            }
       }
        else {
            return 0;
        }

    }
    public function actionAddparams() {
        if (Yii::$app->request->isAjax) {
            $userparams = new UserParams();
            $userparams->hips = $_GET['hips'];
            $userparams->waist = $_GET['waist'];
            $userparams->breast = $_GET['breast'];
            $userparams->weight = $_GET['weight'];

            if ($userparams->save()) {
                //$program = UserProgram::find()->where(['id' => $_GET['user_program_id']])->one();
                $program=UserProgram::getProgramInfo();
                $flag = 0;
                if (empty($program->hips) or $program->hips <= 0) {
                    $program->hips = $_GET['hips'];
                    $flag = 1;
                }
                if (empty($program->waist) or $program->waist <= 0) {
                    $program->waist = $_GET['waist'];
                    $flag = 1;
                }
                if (empty($program->breast) or $program->breast <= 0) {
                    $program->breast = $_GET['breast'];
                    $flag = 1;
                }
                if ($flag == 1) {
                    $program->save();

                }
                return 1;
            } else {
                return 0;
            }
        }
        else {
            return 0;
        }



    }

    public function actionAddcalrashod() {
        if (Yii::$app->request->isAjax) {
            $user_rashod = UserRashod::find()->where(['user_id' => Yii::$app->user->id, 'task_id' => $_GET['task_id']])->andWhere('DATE(FROM_UNIXTIME(created_at)) = CURDATE()')->one();
            if (!empty($user_rashod)) {
                $userrashod = $user_rashod;
            } else {
                $userrashod = new UserRashod();
            }

            $userrashod->task_id = $_GET['task_id'];
            if ($_GET['label']=='steps') {

                $user=UserProfile::find()->where(['user_id' => Yii::$app->user->id])->one();
                $height=$user->height;
                $sex=$user->gender;
                $userparam=UserParams::find()->where(['user_id' => Yii::$app->user->id])->orderBy(['id' => SORT_DESC])->one();
                $weight=$userparam->weight;
                if (!$weight or $weight==0 or $weight==null) {
                    $userprogram=UserProgram::find()->where(['user_id' => Yii::$app->user->id])->orderBy(['id' => SORT_DESC])->one();
                    $weight=$userprogram->weight;
                }
                if ($sex==2) {
                    $k=0.413;
                }
                else {
                    $k=0.415;
                }
                $time=$_GET['duration']/(62/(($height/100)*$k));
                $cal=ceil((((0.007*62*62)+21)*$weight*$time)/1000);



                if ($_GET['cal']>0) {
                    if ($_GET['cal']>$cal+100 || $_GET['cal']<$cal-100) {
                        $userrashod->cal=$cal;
                    }
                    else {
                        $userrashod->cal = $_GET['cal'];
                    }

                }
                else {

                    $userrashod->cal=$cal;
                    //$userrashod->cal=$time;
                }

            }
            else {
                $userrashod->cal = $_GET['cal'];
            }
            if ($_GET['label']=='steps') {
                $userrashod->activity_name='Ходьба';
                $userrashod->duration=$_GET['duration'].' шагов';
            }
            else if ($_GET['label']=='planka') {
                $userrashod->activity_name='Планка';
                $userrashod->duration=$_GET['duration'].' сек.';
            }
            else if ($_GET['label']=='hiit') {
                $userrashod->activity_name='Табата';
                $userrashod->duration=$_GET['duration'].' мин.';
            }
            else if ($_GET['label']=='yoga') {
                $userrashod->activity_name='Йога';
                $userrashod->duration='15 мин.';
            }
            else {
                $userrashod->duration=$_GET['duration'];
            }

            if ($userrashod->save()) {

                //return 1;
                $user_rashod_day = UserRashod::countCalRashod();
                $user_program=UserProgram::getProgramInfo();

                $response = array();
                $response['user_rashod_day'] =  $user_rashod_day+$user_program->cal_norma_day;
                $response['added'] = 1;
                $response['cal'] = $userrashod->cal;
                return json_encode($response);
            }
            else {
                return 0;
            }
        }
        else {
            return 0;
        }





    }
    public function actionAddactivity() {
        if (Yii::$app->request->isAjax) {
            $user_activity = UserRashod::find()->where(['user_id' => Yii::$app->user->id, 'activity_id' => $_GET['activity_id']])->andWhere('DATE(FROM_UNIXTIME(created_at)) = CURDATE()')->one();
            if (!empty($user_activity)) {
                $useractivity = $user_activity;
            } else {
                $useractivity = new UserRashod();
            }
            $userparam=UserParams::find()->where(['user_id' => Yii::$app->user->id])->orderBy(['id' => SORT_DESC])->one();
            $weight=$userparam->weight;
            if (!$weight or $weight==0 or $weight==null) {
                $userprogram=UserProgram::find()->where(['user_id' => Yii::$app->user->id])->orderBy(['id' => SORT_DESC])->one();
                $weight=$userprogram->weight;
            }
            $useractivity->activity_id=$_GET['activity_id'];
            $useractivity->activity_name=$_GET['activity_name'];
            $useractivity->duration=$_GET['duration'];
            $useractivity->cal=ceil($_GET['cal']*$_GET['duration']*$weight);
            if(empty($_GET['duration']) or $_GET['duration']==0) {
                $useractivity->delete();
            }
            else {
                if ($useractivity->save()) {

                    //return 1;


                    $response = $useractivity->cal;
                    //return json_encode($response);
                    return $response;
                }
                else {
                    return 0;
                }
            }

      }
        else {
            return 0;
        }





    }

            public function actionAddtexts() {
       if (Yii::$app->request->isAjax) {
           if (Yii::$app->request->get('id') && !empty(Yii::$app->request->get('id'))) {
               $usertexts=UserTexts::find()->where(['id'=>Yii::$app->request->get('id')])->one();
           }
           if (!$usertexts) {
               $usertexts= new UserTexts();
               $usertexts->user_id=Yii::$app->user->id;
               $usertexts->program_id=UserProgram::getProgramInfo()->id;
               $usertexts->task_id=$_GET['task_id'];
           }


            $usertexts->user_text=$_GET['user_text'];
            if ($usertexts->save()) {
                return 1;
            } else {
                return 0;
            }
       }
        else {
            return 0;
        }

    }
    public function actionUploadphoto()
    {
       // if ($this->validate()) {
           // foreach ($this->imageFile as $file) {
                //echo $file->baseName;exit();

        $model = new UploadForm();

      //  if (Yii::$app->request->isAjax) {
           // $model->load(Yii::$app->request->post());
if(Yii::$app->request->post()) {
            $model->file = UploadedFile::getInstance($model, 'file');

            $task_id=Yii::$app->request->post('taskid');
            $path = Yii::getAlias('@frontend') .'/web/upload/'. date('Ymd').'/'.$task_id.'/';
            FileHelper::createDirectory($path);
            if ($model->file && $model->validate()) {

                $model->file->saveAs($path.'/user_'.Yii::$app->user->id. '.' . $model->file->extension);
                return 1;

            }
            else {
                //return 0;
                return $this->render('photo', ['model' => $model]);
            }
        }
        else {
           // return 0;
            return $this->render('photo', ['model' => $model]);
        }

        //return $this->render('photo', ['model' => $model]);
    }

    public function actionAddusertask()
    {
       if (Yii::$app->request->isAjax) {
            $task = Tasks::find()->where(['id' => $_GET['task_id']])->one();

            $user_task = UserTasks::find()->where(['user_id' => Yii::$app->user->id, 'task_id' => $_GET['task_id']])->andWhere('DATE(FROM_UNIXTIME(created_at)) = CURDATE()')->one();
            if (!empty($user_task)) {
                $model = $user_task;
            } else {
                $model = new UserTasks();
            }
            if ($_GET['label'] == 'water') {
                $model->duration = $model->duration + 1;
            }
            else {
                if (empty($_GET['duration'])) {
                    $model->duration = $_GET['program_duration'];
                } else {
                    $model->duration = $_GET['duration'];
                }
            }
            $model->task_id = $_GET['task_id'];
           $user_program=UserProgram::getProgramInfo();
            $model->user_program_id = $user_program->id;
            $model->program_duration = $_GET['program_duration'];


            if ($model->duration >= $_GET['program_duration']) {
                $model->done = 1;
                $done = 1;
            } else {
                $model->done = 0;
                $done = 0;
            }

            //$model->points=$_GET['points'];
            if ($task->pointsamount == 1 && $_GET['label'] != 'water') {
                $model->points = $task->points;
            } else {
                $model->points = (floor($model->duration / $task->pointsamount)) * $task->points;
            }


            if ($model->save()) {
                UserTasks::getUserPoints($user_program->id);
            }
            $response = array();
            $response['done'] = $done;
            $response['points'] = $model->points;
            return json_encode($response);
            /* if ($model->load(Yii::$app->request->post()) && $model->save()) {
                // return $this->redirect(['view', 'id' => $model->id]);
             } else {
                 return $this->render('create', [
                     'model' => $model,
                 ]);
             }*/
       }

         else {
             return 0;
         }

}
    public function actionActivity() {
        \Yii::$app->view->title='<h1>Дневник активности</h1>';
        //\Yii::$app->view->params['title_show'] = 'no';
        //\Yii::$app->view->params['breadcrumbs'][] = 'Задачи';
        \Yii::$app->view->params['headerclass'] = ' bg-blog';
        \Yii::$app->view->params['title2'] = '';
        $date=Yii::$app->request->get('date');
        if (!$date) {
            $date='CURDATE()';
        }
        else {
            $date='"'.$date.'"';
        }
        $activity = UserRashod::find()->where(['user_id' => Yii::$app->user->id])->andWhere('DATE(FROM_UNIXTIME(created_at)) = '.$date)->all();

        return $this->render('activity', ['activity' => $activity]);
    }
    public function actionExperiment() {
        \Yii::$app->view->title='<h1>Экспериментариум</h1>';
        //\Yii::$app->view->params['title_show'] = 'no';
        //\Yii::$app->view->params['breadcrumbs'][] = 'Задачи';
        \Yii::$app->view->params['headerclass'] = ' bg-blog';
        \Yii::$app->view->params['title2'] = '';
        //$experiment = UserTexts::find()->where(['user_id' => Yii::$app->user->id, 'program_id' => UserProgram::getProgramInfo()->id])->all();
        $user_program=UserProgram::getProgramInfo();
        $interval = date_diff(date_create(date('Y-m-d',$user_program->start_date)), date_create(date('Y-m-d',time())));
        $number_day_program=$interval->format('%R%a')+1;
        $experiment = Tasks::find()->where(['photo' => 'experiment'])->andWhere(['<=','day_from',$number_day_program])->orderBy('period')->all();

        return $this->render('experiment', ['experiment' => $experiment, 'program'=>$user_program]);
    }
    public function actionActivitysearch() {
        if (Yii::$app->request->isAjax && Yii::$app->request->get('search')) {
            $words=explode(' ',Yii::$app->request->get('search'));
            if(count($words)==1) {
                $activity=Activity::find()
                    // ->select(['*','MATCH (title,keywords) AGAINST ('.Yii::$app->request->get('search').') as rel'])
                    ->select(['*'])
                    ->addselect([new \yii\db\Expression('name LIKE "'.Yii::$app->request->get('search').'%" as rel')])
                   // ->Where(['user_id'=>1])
                   // ->orWhere(['user_id'=>Yii::$app->user->id])
                   // ->andWhere(['type'=>'product'])
                    ->andFilterWhere(['like', 'name', Yii::$app->request->get('search').'%', false])
                    ->orFilterWhere(['like', 'name', '% '.Yii::$app->request->get('search').'%', false])
                    ->orderBy(['rel'=>SORT_DESC])
                    ->asArray()
                    ->all();
            }
            else {
                $activity=Activity::find()
                    ->select(['*'])
                    // ->addselect([new \yii\db\Expression('title LIKE "'.$words[0].'%" as rel')])
                    ->addselect([new \yii\db\Expression('MATCH (name,keywords) AGAINST ("'.Yii::$app->request->get('search').'") as rel')])
                   // ->Where(['user_id'=>1])
                  //  ->orWhere(['user_id'=>Yii::$app->user->id])
                  //  ->andWhere(['type'=>'product'])
                    ->Where('MATCH (name,keywords) AGAINST ("'.Yii::$app->request->get('search').'")')
                    //->andFilterWhere(['like', 'title', Yii::$app->request->get('search').'%', false])
                    // ->orFilterWhere(['like', 'title', '% '.Yii::$app->request->get('search').'%', false])
                    ->orderBy(['rel'=>SORT_DESC])
                    ->asArray()
                    ->all();
            }

           
           /* if (empty($activity)) {
                $activity=Activity::find()
                    ->andFilterWhere(['like', 'name', ' '.Yii::$app->request->get('search').'%', false])
                    //->orFilterWhere(['like', 'title', Yii::$app->request->get('search')])
                    ->asArray()
                    ->all();

            }*/
            $json = json_encode($activity);
            /*return $this->render('search',[
                'menu' => $menu,
                'json'=>$json
            ]);*/
            return $json;
         }
         else {
             //  $menu=Products::find()->andFilterWhere(['like', 'title', $_GET['search']])->all();
             return 0;
         }

    }

}
