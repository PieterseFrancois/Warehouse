import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { HomeComponent } from './components/modules/home/home.component';
import { LoginComponent } from './components/modules/login/login.component';
import { RegisterComponent } from './components/modules/register/register.component';
import { ProfileComponent } from './components/modules/profile/profile.component';
import { ProductsAddComponent } from './components/modules/products-add/products-add.component';
import { ProductsViewComponent } from './components/modules/products-view/products-view.component';
import { ChartComponent } from './components/modules/chart/chart.component';
import { AdminGuard } from './global/guards/admin-guard';
import { UserGuard } from './global/guards/user-guard';
import { VisitorGuard } from './global/guards/visitor-guard';

export const routes: Routes = [
    { path: '', title: 'Warehouse', component: HomeComponent },
    { path: 'chart', title: 'Chart', component: ChartComponent, canActivate: [AdminGuard] },
    { path: 'login', title: 'Login', component: LoginComponent, canActivate: [VisitorGuard] },
    { path: 'products/add', title: 'AddProducts', component: ProductsAddComponent, canActivate: [UserGuard]},           //child of products?
    { path: 'products/view', title: 'ViewProducts', component: ProductsViewComponent, canActivate: [UserGuard] },       //child of products?
    { path: 'profile', title: 'Profile', component: ProfileComponent, canActivate: [UserGuard] },
    { path: 'register', title: 'Register', component: RegisterComponent, canActivate: [VisitorGuard] },
    { path: '**', redirectTo: '' }
];

@NgModule({
    imports: [RouterModule.forRoot(routes)],
    exports: [RouterModule]
})

export class AppRoutingModule { }