import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { FormsModule } from '@angular/forms';

import { AppRoutingModule } from './app-routing.module';
import { HttpClientModule, HTTP_INTERCEPTORS } from '@angular/common/http';
import { Interceptor as API_Interceptor } from './global/api_interceptor';
import { AppComponent } from './app.component';

import { NgbModule} from '@ng-bootstrap/ng-bootstrap';
import { DataTablesModule } from 'angular-datatables';

import { HomeComponent } from './components/modules/home/home.component';
import { LoginComponent } from './components/modules/login/login.component';
import { RegisterComponent } from './components/modules/register/register.component';
import { ProfileComponent } from './components/modules/profile/profile.component';
import { NavBarComponent } from './components/nav-bar/nav-bar.component';
import { FooterComponent } from './components/footer/footer.component';
import { ProductsAddComponent } from './components/modules/products-add/products-add.component';
import { ProductsViewComponent } from './components/modules/products-view/products-view.component';
import { ChartComponent } from './components/modules/chart/chart.component';

@NgModule({
    declarations: [
        AppComponent,
        HomeComponent,
        LoginComponent,
        RegisterComponent,
        ProfileComponent,
        NavBarComponent,
        FooterComponent,
        ProductsAddComponent,
        ProductsViewComponent,
        ChartComponent,
    ],
    imports: [
        BrowserModule,
        HttpClientModule,
        NgbModule,
        AppRoutingModule,
        FormsModule,
        DataTablesModule,
    ],
    providers: [
         {
             provide: HTTP_INTERCEPTORS,
             useClass: API_Interceptor,
             multi: true
         }
    ],
    bootstrap: [AppComponent]
})

export class AppModule { }
