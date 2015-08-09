<?php

namespace app\controllers;

use app\components\Git;
use Yii;
use app\models\Package;
use app\models\search\PackageSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ModController implements the CRUD actions for Package model.
 */
class ModController extends Controller
{

    /**
     * Lists all Package models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PackageSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Package model.
     * @param string $name
     * @return mixed
     */
    public function actionView($name)
    {
        return $this->render('view', [
            'model' => $this->findModel($name),
        ]);
    }

    /**
     * Creates a new Package model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Package();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->setFlash('success', Yii::t('app', 'Mod has been registered.'));
            return $this->redirect(['view', 'name' => $model->name]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Package model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $name
     * @return mixed
     */
    public function actionUpdate($name)
    {
        $model = $this->findModel($name);
        $model->bower = Git::getFile($model->url, 'bower.json');
        if (!$model->bower) {
            Yii::$app->getSession()->setFlash('danger', Yii::t('app', 'bower.json could not be loaded from remote repository.'));
            return $this->redirect(['view', 'name' => $model->name]);
        }
        $model->setBowerData();
        if ($model->getDirtyAttributes()) {
            $model->save();
        }
        Yii::$app->getSession()->setFlash('success', Yii::t('app', 'bower.json has been updated from remote repository.'));
        return $this->redirect(['view', 'name' => $model->name]);
    }

    /**
     * Finds the Package model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $name
     * @return Package the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($name)
    {
        if (($model = Package::find()->where(['name' => $name])->one()) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
