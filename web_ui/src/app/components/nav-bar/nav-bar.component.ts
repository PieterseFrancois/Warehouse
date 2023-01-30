import { Component, OnDestroy } from '@angular/core';
import { Subject, takeUntil } from 'rxjs';
import { routes } from 'src/app/app-routing.module';;
import { UserState } from 'src/app/global/state/user-state';
import { User } from 'src/app/global/types';

@Component({
    selector: 'app-nav-bar',
    templateUrl: './nav-bar.component.html',
    styleUrls: ['./nav-bar.component.scss']
})

export class NavBarComponent implements OnDestroy {

    //website navigation descriptions
    website_name: string = "Warehouse";
    chart_page: string = "Chart"
    all_products: string = "View all";
    add_products: string = "Add new";

    //profile navigation
    username: string = "John Doe";

    //conditions to display navigation components
    is_user: boolean = false;
    is_admin: boolean = false;

    //routerLinks
    add_products_route: string = routes.find( element => element.title === 'AddProducts' ).path;
    all_products_route: string = routes.find( element => element.title === 'ViewProducts' ).path;
    chart_route: string = routes.find( element => element.title === 'Chart' ).path;
    login_route: string = routes.find( element => element.title === 'Login' ).path;
    logout_route: string = this.login_route;
    profile_route: string = routes.find( element => element.title === 'Profile' ).path;
    register_route: string = routes.find( element => element.title === 'Register' ).path;

    private destroy$: Subject<boolean> = new Subject();

    constructor( public user_state: UserState ) {

        this.user_state.current_user$
        .pipe( takeUntil( this.destroy$ ) )
        .subscribe(
            ( user: User ) => {

                if ( user === undefined )
                {
                    this.is_user = false;
                    this.is_admin = false; 

                    return;
                }
                
                this.username = user.name;

                this.is_user = true;

                this.is_admin = ( user.role === 'admin' );
            }
        );
    }

    ngOnDestroy(): void {
        this.destroy$.next(true);
        this.destroy$.complete();
    }

}
